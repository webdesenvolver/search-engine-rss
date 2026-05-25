<?php
/**
 * INSTALL.md - Guia de Instalação Completo
 */
?>
# 📦 Guia de Instalação - Search Engine RSS

## ⚙️ Pré-requisitos

### Servidor
- **PHP 7.4 ou superior**
- **Apache 2.4+** com mod_rewrite
- **Acesso via SSH** (recomendado)

### Extensões PHP Necessárias
```bash
php -m | grep -E "json|dom|xmlreader"
```

Devem exibir:
- ✅ json
- ✅ dom
- ✅ xmlreader

Se não estiverem instaladas:

**CentOS/RHEL:**
```bash
yum install php-xml php-json
systemctl restart apache2
```

**Ubuntu/Debian:**
```bash
apt-get install php-xml php-json
systemctl restart apache2
```

---

## 🚀 Instalação Passo a Passo

### 1. Clone o Repositório

```bash
cd /var/www/html
git clone https://github.com/webdesenvolver/search-engine-rss.git
cd search-engine-rss
```

Ou **sem git**:
```bash
wget https://github.com/webdesenvolver/search-engine-rss/archive/main.zip
unzip main.zip
mv search-engine-rss-main search-engine-rss
cd search-engine-rss
```

### 2. Permissões de Arquivo

```bash
# Permissões normais
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Permissões de escrita para diretórios de dados
chmod -R 777 data/
chmod -R 777 logs/

# Se necessário, mude o proprietário
chown -R www-data:www-data .
chown -R www-data:www-data data/
chown -R www-data:www-data logs/
```

**Windows (IIS):**
```
- Clique direito em: data/ > Propriedades > Segurança
- Adicione "Usuários do IIS" com permissão "Modificar"
```

### 3. Configurar Google Custom Search Engine

#### Obter API Key:
1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto:
   - Nome: "Search Engine RSS"
   - Clique em "Criar"

3. Ative a API:
   - Pesquise por "Custom Search API"
   - Clique em "Ativar"

4. Crie credenciais:
   - Clique em "Criar credenciais"
   - Tipo: "Chave de API"
   - Copie a chave

#### Obter CSE ID:
1. Acesse [Programmable Search Engine](https://programmablesearchengine.google.com/)
2. Clique em "Create"
3. Defina:
   - **Sitios para buscar:** `*.com` (ou conforme necessário)
   - **Nome do mecanismo:** "Search Engine RSS"
4. Clique em "Criar"
5. Na aba "Setup", copie o **Código do mecanismo de busca** (cx)

#### Adicione em config.php:
```php
<?php
define('GOOGLE_CSE_API_KEY', 'SUA_CHAVE_API');
define('GOOGLE_CSE_ID', 'SEU_CSE_ID');
```

### 4. Adicionar Feeds RSS (Opcional)

Edite `data/feeds.json` ou use o painel admin.

**Exemplo de feeds válidos:**
```
- BBC News: http://feeds.bbc.co.uk/news/rss.xml
- Reuters: https://www.reutersagency.com/feed/
- TechCrunch: http://feeds.feedburner.com/TechCrunch/
- HackerNews: https://news.ycombinator.com/rss
- Reddit r/programming: https://www.reddit.com/r/programming/.rss
```

### 5. Configurar Apache (VirtualHost)

Se não estiver em um subdiretório, crie um VirtualHost:

**Arquivo: `/etc/apache2/sites-available/search-engine-rss.conf`**

```apache
<VirtualHost *:80>
    ServerName search.seu-dominio.com
    ServerAlias www.search.seu-dominio.com
    
    DocumentRoot /var/www/search-engine-rss
    
    <Directory /var/www/search-engine-rss>
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php?/$1 [L]
        </IfModule>
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/search-engine-rss-error.log
    CustomLog ${APACHE_LOG_DIR}/search-engine-rss-access.log combined
    
    # Compressão
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript
    </IfModule>
</VirtualHost>
```

**Ativar:**
```bash
a2ensite search-engine-rss.conf
a2enmod rewrite
systemctl restart apache2
```

### 6. Configurar Cron Job (Atualizar RSS)

**Para atualizar feeds automaticamente a cada hora:**

```bash
crontab -e
```

Adicione ao final:
```cron
0 * * * * php -f /var/www/search-engine-rss/cron/fetch-feeds.php >> /var/www/search-engine-rss/logs/cron.log 2>&1
```

**Outras frequências:**
```cron
# A cada 30 minutos
*/30 * * * * php -f /caminho/cron/fetch-feeds.php

# A cada 6 horas
0 */6 * * * php -f /caminho/cron/fetch-feeds.php

# Diariamente às 3 da manhã
0 3 * * * php -f /caminho/cron/fetch-feeds.php
```

**Via cPanel (Alternativa):**
1. Acesse cPanel → Cron Jobs
2. Adicione novo cron job:
   - **Comando:** `php -f /home/usuario/public_html/search-engine-rss/cron/fetch-feeds.php`
   - **Frequência:** Horária

### 7. SSL/HTTPS (Recomendado)

```bash
# Instalar Let's Encrypt
apt-get install certbot python3-certbot-apache

# Gerar certificado
certbot --apache -d search.seu-dominio.com

# Auto-renovação
systemctl enable certbot.timer
systemctl start certbot.timer
```

### 8. Testar Instalação

**1. Teste PHP:**
```bash
php -v
php config.php
```

**2. Teste de Conectividade:**
```bash
# Teste RSS
php -f cron/fetch-feeds.php

# Teste Google CSE
curl "http://localhost/search-engine-rss/api/search.php?action=search&q=test&source=google"
```

**3. Via navegador:**
- Página principal: `http://seu-dominio.com/search-engine-rss/`
- Painel admin: `http://seu-dominio.com/search-engine-rss/admin/`
- Sitemap: `http://seu-dominio.com/search-engine-rss/sitemap.xml`

---

## 🔍 Troubleshooting

### Erro 500 - Internal Server Error

**Verificar logs:**
```bash
tail -f /var/log/apache2/error.log
tail -f logs/*.log
```

**Solução:**
- Verifique permissões: `chmod -R 777 data/ logs/`
- Verifique se PHP está instalado: `php -v`
- Verifique mod_rewrite: `a2enmod rewrite && systemctl restart apache2`

### "Google CSE não configurado"

**Solução:**
- Verifique `config.php`
- Confira se as chaves estão corretas
- Teste com: `curl "https://www.googleapis.com/customsearch/v1?q=test&cx=SEU_ID&key=SUA_CHAVE"`

### "Permissão negada em data/"

```bash
chmod -R 777 data/ logs/
chown -R www-data:www-data data/ logs/
```

### Feeds RSS não atualizam

**Verificar:**
1. Se cron está rodando: `grep CRON /var/log/syslog`
2. Testar manualmente: `php -f cron/fetch-feeds.php`
3. Verificar se URLs são válidas
4. Verificar conexão com internet do servidor

### Erro de Timeout

**Aumentar timeout em `config.php`:**
```php
define('RSS_CHECK_INTERVAL', 7200); // 2 horas
define('RSS_CACHE_TIME', 3600);     // 1 hora
```

---

## 📊 Verificação Final

```bash
# Testar estrutura
tree search-engine-rss/

# Verificar permissões
ls -la search-engine-rss/data/
ls -la search-engine-rss/logs/

# Testar PHP
php search-engine-rss/index.php

# Verificar arquivo de configuração
grep "GOOGLE_CSE" search-engine-rss/config.php
```

---

## 🎯 Próximos Passos

1. ✅ Fazer upload para servidor
2. ✅ Configurar Google CSE
3. ✅ Testar busca
4. ✅ Adicionar feeds RSS
5. ✅ Configurar cron job
6. ✅ Configurar SSL
7. ✅ Registrar em buscadores (Google Search Console, Bing Webmaster)

---

## 📞 Suporte

- **Documentação:** Veja `README.md`
- **Issues:** GitHub Issues
- **Email:** seu-email@dominio.com

---

**Pronto! Seu Search Engine RSS está funcionando! 🎉**
