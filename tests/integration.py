#!/usr/bin/env python3
"""Smoke test real de endpoints PHP usando MySQL efêmero na CI."""
import http.cookiejar
import re
import subprocess
import urllib.error
import urllib.parse
import urllib.request

BASE = "http://127.0.0.1:8080/"
opener = urllib.request.build_opener(
    urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar())
)


def call(path, fields=None):
    body = None if fields is None else urllib.parse.urlencode(fields).encode("utf-8")
    req = urllib.request.Request(
        urllib.parse.urljoin(BASE, path), data=body,
        headers={"Content-Type": "application/x-www-form-urlencoded"}
    )
    try:
        with opener.open(req, timeout=15) as response:
            return response.status, response.read().decode("utf-8")
    except urllib.error.HTTPError as error:
        return error.code, error.read().decode("utf-8")


def token():
    status, body = call("login.php")
    assert status == 200, ("login page", status)
    match = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body)
    assert match, "token CSRF ausente"
    return match.group(1)


csrf = token()
status, body = call("api/auth.php", {
    "action": "register", "csrf_token": csrf,
    "nome": "Teste Integracao", "telefone": "15999999999",
    "email": "teste@example.com", "senha": "Senhateste123!",
    "tipo": "funcionario",  # não pode alterar perfil via formulário
})
assert status == 200 and "Conta criada" in body, ("cadastro", status, body[:200])

status, body = call("api/auth.php", {
    "action": "login", "csrf_token": csrf,
    "email": "teste@example.com", "senha": "Senhateste123!",
})
assert status == 200 and "Encontre a peça ideal" in body, ("login", status, body[:200])

status, _ = call("estoque.php")
assert status == 403, "cadastro público não pode abrir estoque"

subprocess.run([
    "php", "-r",
    """$pdo=new PDO('mysql:host=127.0.0.1;dbname=estoque;charset=utf8mb4','root','rootpass');
    $pdo->exec("UPDATE usuarios SET tipo='funcionario' WHERE email='teste@example.com'");"""
], check=True)

csrf = token()
status, _ = call("api/logout.php", {"csrf_token": csrf})
assert status == 200, ("logout falhou", status, _[:400])
csrf = token()
status, body = call("api/auth.php", {
    "action": "login", "csrf_token": csrf,
    "email": "teste@example.com", "senha": "Senhateste123!",
})
assert status == 200, "login de funcionário falhou"

status, _ = call("api/products.php", {
    "action": "create", "csrf_token": csrf,
    "nome": "Filtro de óleo", "categoria": "Motor", "modelo": "Uno",
    "ano": "2010", "preco": "49,90", "quantidade": "5",
})
assert status == 200, ("cadastro peça", status)
status, body = call("shopping.php?q=Filtro")
assert status == 200 and "Filtro de óleo" in body and "5 em estoque" in body, "catálogo não reflete estoque"

status, _ = call("api/products.php", {
    "action": "adjust", "csrf_token": csrf, "id": "1", "delta": "-2",
})
assert status == 200, "saída de estoque falhou"
status, body = call("shopping.php?q=Filtro")
assert "3 em estoque" in body, "estoque não foi reduzido"

status, body = call("api/products.php", {
    "action": "adjust", "csrf_token": csrf, "id": "1", "delta": "-500",
})
assert status == 200 and "Não foi possível ajustar" in body, "saldo negativo não bloqueado"
status, body = call("shopping.php?q=Filtro")
assert "3 em estoque" in body, "saldo alterado após transação rejeitada"

status, _ = call("api/products.php", {
    "action": "create", "csrf_token": "token-invalido",
    "nome": "Fraude", "categoria": "Outros", "preco": "1", "quantidade": "3",
})
assert status == 403, "CSRF não bloqueado"
print("PASSOU: cadastro, login, permissões, catálogo, entradas/saídas, saldo não-negativo e CSRF")
