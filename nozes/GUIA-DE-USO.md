# Guia de Uso — Tema Nozes

Este guia explica, em português simples, como instalar e usar o tema no seu WordPress da Hostinger. Não é preciso saber programação para nenhum destes passos.

## 1. Instalar o tema na Hostinger

1. Baixe o arquivo `nozes.zip` (está na raiz deste repositório, gerado a partir da pasta `nozes/`).
2. No seu WordPress, vá em **Aparência → Temas → Adicionar novo → Enviar tema**.
3. Selecione o `nozes.zip` e clique em **Instalar agora** e depois em **Ativar**.
4. Ao ativar, o tema já cria sozinho as páginas iniciais (Início, Sobre, Serviços, Contato, Área do Cliente), um menu principal e define a página inicial — isso só acontece na primeira ativação, então é seguro reativar depois sem duplicar nada.

## 2. O que revisar logo depois de ativar

O tema usa textos de exemplo com a voz da Nozes, mas **alguns trechos estão marcados como "AJUSTE"** porque não conseguimos acessar automaticamente o conteúdo do site atual (somosnozes.com.br) para migrar — o servidor bloqueou o acesso automático. Revise principalmente:

- Página **Serviços** e a seção de serviços da **Início**: confirme os nomes/descrições dos serviços existentes (deixamos 2-3 como placeholder).
- Página **Sobre**: texto de apresentação da empresa/fundadora.
- Depoimentos na Início: são só exemplos, troque por depoimentos reais.

O novo serviço **"Assessoria em Decisões de Negócio e Valorização de Marca"** já está com o texto definitivo, incluindo o destaque visual de "novo" e a FAQ deixando claro que o plano de ações imediatas não está incluso.

## 3. Configurar o essencial (sem precisar de código)

Vá em **Aparência → Personalizar → Configurações da Nozes**:

- **WhatsApp**: número (formato `554830507538`, com código do país) e a mensagem inicial. Confirme que este é o número realmente cadastrado no WhatsApp/WhatsApp Business.
- **Contato e Endereço**: telefone de exibição, e-mail, cidade — usados no rodapé e nos dados estruturados de SEO.
- **Redes Sociais**: Instagram e LinkedIn.
- **SEO e Dados Estruturados**: o resumo da empresa (usado também no arquivo `/llms.txt`, explicado abaixo).

Em **Aparência → Logotipo do Site** você troca a logo pelo arquivo oficial quando quiser (já vai um placeholder com a marca enviada).

## 4. Editando o conteúdo no dia a dia

Você escolheu o **Elementor** como editor visual. Fluxo recomendado:

1. Instale o plugin gratuito **Elementor** (Plugins → Adicionar novo → buscar "Elementor").
2. Abra a página que quer editar e clique em **Editar com Elementor**.
3. Na primeira vez, defina as **Cores e Fontes Globais** do Elementor com a paleta oficial (veja tabela abaixo) e a fonte **Poppins** — assim qualquer coisa nova que você montar no Elementor já nasce no padrão visual da marca.
4. O cabeçalho (menu) e o rodapé são fixos no código do tema (para manter consistência e não quebrar o botão de WhatsApp/links), então o Elementor edita apenas o conteúdo de cada página — igual a maioria dos sites profissionais.

Se preferir não usar Elementor num dia a dia mais simples, o **editor nativo do WordPress** (blocos) também funciona normalmente em qualquer página.

### Paleta oficial (para configurar no Elementor)

| Uso | Cor | Hex |
|---|---|---|
| Base | Preto | `#1D1D1F` |
| Acento primário | Rosa | `#FF0066` |
| Acento primário | Verde limão | `#ABF705` |
| Acento primário | Amarelo limão | `#D8FF01` |
| Acento secundário | Azul | `#1FBDC6` |
| Acento secundário | Amarelo | `#FFD100` |
| Acento terciário | Coral | `#FF6B6B` |
| Acento terciário | Roxo | `#7B4FE0` |

Fonte: **Poppins** (já vem instalada localmente no tema, pesos 400/500/600/700/800).

## 5. Subir uma página pronta feita aqui pelo Claude

Sempre que eu montar uma página especial para você (uma landing page de campanha, por exemplo), o processo é:

1. Crie uma página nova em **Páginas → Adicionar nova**.
2. No painel direito, em **Atributos da página → Modelo**, escolha **"Página HTML Livre"**.
3. Troque o editor de blocos para o modo **Código** (os três pontinhos no canto superior direito → "Editor de código") ou adicione um bloco **HTML personalizado**.
4. Cole o HTML completo que eu te enviar e publique.

Esse modelo mostra o conteúdo em largura total, mas mantém o cabeçalho e rodapé do site (menu e marca continuam presentes). Se um dia você quiser uma página **totalmente em branco** (sem menu/rodapé do tema), me avise — é só pedir esse tipo de página que eu preparo.

## 6. Área do Cliente (relatórios mensais)

Cada cliente tem **login individual** e só vê os próprios relatórios.

### Criar um cliente novo

1. **Usuários → Adicionar novo**.
2. Preencha nome/e-mail, e em **Função** escolha **"Cliente"**.
3. Defina uma senha e envie usuário+senha para o cliente (por e-mail ou WhatsApp).
4. O cliente acessa pela página **Área do Cliente** (já está no menu e no rodapé do site).

### Publicar um relatório mensal

1. No menu lateral do admin, vá em **Relatórios (Clientes) → Adicionar novo**.
2. Dê um título (ex: "Relatório — Julho 2026").
3. Troque o editor para o modo **Código** e cole o HTML do relatório.
4. No painel direito, em **Autor**, selecione o cliente dono deste relatório.
5. Publique. Ele aparece automaticamente na Área do Cliente daquele usuário, mais recente primeiro — e nenhum outro cliente consegue ver, porque não existe um link público para o relatório: tudo é listado só depois do login.

## 7. SEO e GEO (otimização para buscadores e para IA)

O tema já vem com, sem precisar instalar nada:

- Título, meta descrição, Open Graph e Twitter Card em todas as páginas.
- Dados estruturados (JSON-LD) de **Organização/Negócio Local**, **Artigo** (posts do blog) e **Trilha de navegação (breadcrumbs)**.
- Um shortcode de **FAQ** (`[nozes_faq]`) que gera automaticamente o schema de perguntas e respostas — use-o em qualquer página pelo bloco "Shortcode" ou pelo widget de Shortcode do Elementor.
- Um arquivo `/llms.txt` (ex: `somosnozes.com.br/llms.txt`) com um resumo da empresa, contato e páginas principais — pensado para ferramentas de IA (ChatGPT, Perplexity, etc.) entenderem rapidamente quem é a Nozes.

**Se você instalar um plugin de SEO** (recomendamos o **Rank Math**, gratuito) para ter sitemap, redirecionamentos e mais controle fino de SEO:

- O tema detecta automaticamente o Rank Math/Yoast e para de gerar título/meta description por conta própria, para não conflitar.
- Se ativar os "dados estruturados" (schema) dentro do próprio Rank Math, marque a opção **"Desativar dados estruturados do tema"** em Personalizar → SEO, para não duplicar informações para o Google.

## 8. Plugins recomendados (gratuitos)

- **Elementor** — editor visual das páginas.
- **Rank Math** ou **Yoast SEO** — sitemap.xml, redirecionamentos, painel de SEO por página.
- **WPForms Lite** (ou similar) — caso queira um formulário de contato além do WhatsApp.
- Um plugin de cache (ex: **WP Super Cache** ou o cache nativo da Hostinger) — a Hostinger já otimiza boa parte disso no servidor.

## 9. Resumo do que foi construído

- Tema WordPress completo e independente (não depende de nenhum outro tema).
- Compatível com Elementor e com o editor nativo do WordPress ao mesmo tempo.
- Paleta e tipografia oficiais da Nozes aplicadas em todo o site.
- Botão de WhatsApp fixo (flutuante) em todas as páginas + botão no menu.
- Novo serviço "Assessoria em Decisões de Negócio e Valorização de Marca" já estruturado e em destaque.
- Área do Cliente com login individual e relatórios em HTML por cliente.
- Modelo de "Página HTML Livre" para publicar páginas prontas feitas por aqui.
- SEO técnico + dados estruturados + FAQ com schema + `/llms.txt` para otimização em buscadores de IA (GEO).
