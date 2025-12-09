# Demo estática (GitHub Pages)

Esta pasta `docs/` contém uma **demonstração somente visual** do projeto.
O GitHub Pages **não executa PHP/MySQL**, por isso os dados são carregados do `receitas.json` e o botão de carrinho está desativado.

## Como publicar
1. Faz commit e push desta pasta `docs/` para o repositório.
2. No GitHub, abre **Settings → Pages**:
   - Source: **Deploy from a branch**
   - Branch: **main** • Folder: **/docs**
3. Acede: `https://IFernandes27.github.io/Projecto_Receita_php/`

## Ajustar imagens
- Coloca imagens em `docs/assets/` e atualiza o campo `imagem` no `receitas.json`, por exemplo:
  "assets/bolo.jpg".

## Demo funcional (PHP/MySQL)
Para uma demo com backend, usa um alojamento com PHP + MySQL e faz deploy via FTP/GitHub Actions.
