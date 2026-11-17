# Deploy do TopCasa (Hostinger)

Guia para colocar o site novo em produção na mesma conta Hostinger (`u855228810`)
que já aloja o site antigo. Segue esta ordem — a rotação de segredos é o passo
mais importante e deve ser feita **antes** de o site novo ficar acessível
publicamente.

## 1. Confirmar o plano Hostinger

No hPanel, confirma:

- **Versão do PHP**: 8.2 ou superior (Avançado → PHP Configuration). O site
  antigo corria em PHP 7.2 (obsoleto); o novo exige 8.2+.
- **Acesso SSH**: disponível nos planos Business/Cloud da Hostinger. Se não
  tiveres SSH, o Composer ainda pode correr via hPanel (Avançado → SSH Access
  ou via um script de deploy), mas SSH torna tudo mais simples.
- **Cron jobs**: disponíveis em todos os planos (Avançado → Cron Jobs).

## 2. Rodar TODOS os segredos expostos no código antigo

O código antigo tinha estas credenciais em texto simples no repositório. Têm
de ser trocadas **antes** do lançamento — não é opcional, mesmo que o site
antigo deixe de estar online:

| Segredo | Onde mudar | Onde configurar no novo site |
|---|---|---|
| Password da base de dados (`u855228810_topcasauser`) | hPanel → Bases de Dados MySQL → Alterar password | `DB_PASSWORD` no `.env` |
| Password SMTP (`no-reply@topcasa.pt`) | hPanel → Emails → Alterar password | `MAIL_PASSWORD` no `.env` (até teres isto, `MAIL_MAILER=log` funciona como solução temporária) |
| Groq API key | [console.groq.com](https://console.groq.com/keys) → revogar a antiga, criar nova | `GROQ_API_KEY` no `.env` |
| Login do painel de admin (credenciais hardcoded no código antigo — removidas deste documento) | Já não existe — o novo painel usa contas reais (`user_type=admin`) com password normal | Confirma que a conta `admin` importada (Fase 11) tem uma password forte definida (ver passo 6) |

> **Login com Google removido**: por decisão posterior, o login/registo com
> Google foi retirado do site (fica só email+password). Não é preciso rodar
> nem configurar nenhum Google OAuth Client Secret — essas linhas foram
> removidas deste guia.

## 3. Preparar o `.env` de produção

Copia `.env.example` para `.env` no servidor e preenche:

```
APP_NAME=TopCasa
APP_ENV=production
APP_DEBUG=false
APP_URL=https://topcasa.pt
APP_KEY=            # gerar com `php artisan key:generate` no servidor

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=u855228810_topcasadb
DB_USERNAME=u855228810_topcasauser
DB_PASSWORD=         # a NOVA password do passo 2

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_SCHEME=ssl
MAIL_USERNAME=no-reply@topcasa.pt
MAIL_PASSWORD=       # a NOVA password do passo 2
MAIL_FROM_ADDRESS=no-reply@topcasa.pt

GOOGLE_CLIENT_ID=1003821902914-9rn492v18qp7g0qblv1lua6bbi5eori2.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=   # o NOVO secret do passo 2
GOOGLE_REDIRECT_URI=https://topcasa.pt/auth/google/callback

GROQ_API_KEY=        # a NOVA key do passo 2

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```

**Nunca** commites este `.env` para o git — já está no `.gitignore`.

## 4. Estrutura de pastas no Hostinger

O Hostinger serve o domínio a partir de `public_html/`, mas uma aplicação
Laravel só deve expor a pasta `public/`. Duas opções:

**Opção recomendada** — projeto fora de `public_html`, ligado por conteúdo:

1. Envia o projeto completo para `~/topcasa-app/` (fora de `public_html`).
2. Copia o **conteúdo** de `~/topcasa-app/public/` para dentro de
   `public_html/`.
3. Edita `public_html/index.php` e ajusta os dois `require` para apontarem
   para `../topcasa-app/vendor/autoload.php` e
   `../topcasa-app/bootstrap/app.php`.

## 5. Deploy (via SSH)

```bash
cd ~/topcasa-app
git clone <repo> .          # ou git pull, se já existir
composer install --no-dev --optimize-autoloader
cp .env.example .env        # depois preencher como no passo 3
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci && npm run build
```

## 6. Migrar os dados reais

Só depois da app estar a correr com uma base de dados vazia:

```bash
# Restaura o dump antigo para uma base de dados separada (ex: topcasa_legado)
# no mesmo servidor MySQL, depois:
php artisan app:migrar-dados-legado --source=/caminho/para/_public_html
```

Isto importa utilizadores, anúncios, mensagens, orçamentos, recrutamento,
newsletter, blog, banners, empresas apoiadoras e alertas — ver o commit da
Fase 11 para o mapeamento completo. A conta `admin` importada mantém a
password antiga (hash bcrypt legado, compatível) — usa "Esqueci-me da
password" para a redefinir antes de dares o login a alguém.

## 7. Cron (hPanel → Avançado → Cron Jobs)

Duas entradas, ambas a cada minuto:

```
* * * * * php /home/u855228810/topcasa-app/artisan schedule:run >> /dev/null 2>&1
* * * * * php /home/u855228810/topcasa-app/artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

- A primeira despoleta o scheduler do Laravel (expiração de anúncios, avisos,
  lembretes, gerador de blog/glossário — ver Fase 8).
- A segunda processa a fila de emails/notificações (newsletter, alertas em
  massa) — hosting partilhado não suporta um worker persistente, por isso
  processa a fila em rajadas de até 50s a cada minuto, o que é suficiente
  para o volume deste site.

## 8. Verificação pós-deploy

- [ ] `https://topcasa.pt/` carrega sem erros
- [ ] Login com a conta admin importada funciona (depois de redefinir a password)
- [ ] Publicar um anúncio de teste, aprovar no `/admin`, confirmar que aparece em `/anuncios`
- [ ] Login com Google funciona (testa com uma conta pessoal)
- [ ] Um email de teste chega (ex: pedir reset de password)
- [ ] `/sitemap.xml` responde e lista os anúncios/posts reais
- [ ] `php artisan schedule:list` no servidor mostra as 5 tarefas agendadas
- [ ] Confirmar que `APP_DEBUG=false` (nunca mostrar stack traces a visitantes)

## 9. Site antigo

Depois de confirmar que tudo funciona, atualiza o DNS/domínio para apontar
para o site novo. Mantém o backup do `_public_html` e do dump SQL antigos
num local seguro por uns meses, para o caso de precisares de consultar algo,
mas **não** os deixes acessíveis publicamente (têm as credenciais antigas
hardcoded).

## Deploy no staging

O docroot do staging é `~/domains/topcasa.pt/public_html/staging`. O
`index.php` dessa pasta chama `usePublicPath(__DIR__)`, mas o `php artisan`
não passa por ele. No `.env` do staging:

```
APP_PUBLIC_PATH=/home/u855228810/domains/topcasa.pt/public_html/staging
```

Sem esta linha, `config:cache` (e qualquer comando que arranque a app pela
CLI) aponta o disco `public` para `~/domains/topcasa.pt/storage`, a pasta de
produção. Em produção a variável fica vazia: o fallback continua a ser a
pasta imediatamente acima da aplicação.

O `proc_open` está desligado neste PHP, e o `composer install` normal corre
`package:discover` no fim. Se esse script falha, pode apagar
`bootstrap/cache/packages.php`. Instala sem scripts e descobre os pacotes à
parte:

```bash
cd ~/topcasa-app
git pull origin staging
composer install --no-dev --optimize-autoloader --no-scripts
php artisan package:discover
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Não é preciso Node neste servidor: os assets compilados já vêm no repositório.
