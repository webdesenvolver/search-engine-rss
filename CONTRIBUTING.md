# Contributing - Contribuindo

Obrigado por considerar contribuir para o Search Engine RSS! 🙏

## 📋 Como Contribuir

### 1. Reportar Bugs

**Antes de reportar:**
- ✅ Verifique se o bug já foi reportado
- ✅ Tente reproduzir o problema
- ✅ Colete informações do seu sistema

**Ao reportar, inclua:**
```markdown
**Descrição do bug:**
Uma descrição clara do problema

**Passos para reproduzir:**
1. Vá para '...'
2. Clique em '...'
3. Veja o erro: '...'

**Comportamento esperado:**
O que deveria acontecer

**Screenshots:**
[Se aplicável]

**Sistema:**
- OS: [ex: Ubuntu 20.04]
- PHP: [ex: 7.4]
- Apache: [ex: 2.4]
```

### 2. Sugerir Melhorias

**Template:**
```markdown
**É relacionado a um problema?**
Descreva o problema que este recurso resolveria

**Descreva a solução que gostaria:**
Descrição clara da solução

**Contexto adicional:**
Qualquer informação adicional
```

### 3. Pull Requests

#### Preparar seu fork:
```bash
# 1. Fork no GitHub
# 2. Clone seu fork
git clone https://github.com/seu-usuario/search-engine-rss.git
cd search-engine-rss

# 3. Crie uma branch
git checkout -b fix/meu-fix
# ou
git checkout -b feature/minha-feature

# 4. Faça as mudanças
# 5. Commit
git add .
git commit -m "Descrição clara do que foi mudado"

# 6. Push
git push origin fix/meu-fix

# 7. Crie PR no GitHub
```

#### Padrão de commit:
```
[tipo]: descrição curta

Descrição mais detalhada se necessário.

Closes #123  (se fecha uma issue)
```

**Tipos:**
- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação (sem mudanças lógicas)
- `refactor:` Refatoração
- `test:` Testes
- `chore:` Manutenção

**Exemplo:**
```
feat: adicionar suporte a Dark Mode

- Detecta preferência do sistema
- Salva preferência do usuário
- Aplica tema dinamicamente
- Testes unitários adicionados

Closes #42
```

---

## 🎨 Guias de Estilo

### PHP
```php
<?php
// Use PSR-12

// Classes
class MyClass {
    private $property;
    
    public function __construct() {
        // ...
    }
    
    public function myMethod() {
        // ...
    }
}

// Indentação: 4 espaços
// Sem tabs

// Comentários
/**
 * Descrição do método
 * @param type $param Descrição
 * @return type Descrição
 */
public function myMethod($param) {
    // ...
}
```

### JavaScript
```javascript
// Use ES6+

// Variáveis
const CONSTANT = 'value';
let variable = 'value';

// Classes
class MyClass {
    constructor() {
        this.property = 'value';
    }
    
    myMethod() {
        // ...
    }
}

// Funções
function myFunction(param) {
    // ...
}

// Arrow functions
const arrowFunction = (param) => {
    // ...
};

// Indentação: 4 espaços
// Use const por padrão, let se necessário
```

### CSS
```css
/* Use SMACSS + BEM para classes */

/* Bloco */
.search-form {
    /* ... */
}

/* Elemento */
.search-form__input {
    /* ... */
}

/* Modificador */
.search-form--active {
    /* ... */
}

/* Indentação: 4 espaços */
/* Organize por: Layout, Tipografia, Cor, Outras */
```

---

## 🧪 Testes

### PHP
```bash
# Instalar PHPUnit
composer require phpunit/phpunit --dev

# Rodar testes
./vendor/bin/phpunit tests/
```

### JavaScript
```bash
# Instalar Jest
npm install jest --save-dev

# Rodar testes
npm test
```

---

## 📦 Processo de Review

1. ✅ Verifique se o código segue os padrões
2. ✅ Adicione testes para novas funcionalidades
3. ✅ Atualize documentação
4. ✅ Execute testes localmente
5. ✅ Crie pull request com descrição clara
6. ✅ Responda comentários do review
7. ✅ Aguarde aprovação dos mantenedores

---

## 📚 Recursos

- [PHP PSR-12](https://www.php-fig.org/psr/psr-12/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)

---

## ❓ Dúvidas?

- 💬 Abra uma discussion no GitHub
- 📧 Envie um email para: seu-email@dominio.com
- 💬 Discord: [Link do seu servidor]

---

**Obrigado por contribuir! 🚀**
