#!/bin/bash

################################################################################
# Script para Copiar Templates a Nuevo Proyecto WordPress
# Autor: Cristian Farfan
# Versión: 1.0.0
################################################################################

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  🚀 Crear Nuevo Plugin WordPress - Cristian Farfan           ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""

# 1. Solicitar nombre del nuevo plugin
echo -e "${YELLOW}📝 Información del Nuevo Plugin${NC}"
echo ""
read -p "Nombre del plugin (ej: Mi Awesome Plugin): " PLUGIN_NAME
read -p "Slug del plugin (ej: mi-awesome-plugin): " PLUGIN_SLUG
read -p "Package name (ej: Mi_Awesome_Plugin): " PLUGIN_PACKAGE
read -p "Prefijo de funciones (ej: map): " PLUGIN_PREFIX
read -p "Prefijo de constantes (ej: MAP): " PLUGIN_PREFIX_CONST
read -p "Descripción corta: " PLUGIN_DESCRIPTION

# 2. Crear directorio del nuevo plugin
NEW_PLUGIN_DIR="../${PLUGIN_SLUG}"

echo ""
echo -e "${BLUE}📁 Creando directorio: ${NEW_PLUGIN_DIR}${NC}"

if [ -d "$NEW_PLUGIN_DIR" ]; then
    echo -e "${RED}❌ Error: El directorio ya existe!${NC}"
    exit 1
fi

mkdir -p "$NEW_PLUGIN_DIR"

# 3. Copiar templates
echo -e "${BLUE}📋 Copiando templates...${NC}"

cp -r . "$NEW_PLUGIN_DIR/.dev-templates/"

# 4. Crear estructura de carpetas
echo -e "${BLUE}🏗️  Creando estructura de carpetas...${NC}"

cd "$NEW_PLUGIN_DIR"

mkdir -p assets/{css,js,images,screenshots}
mkdir -p assets/css/{admin,public}
mkdir -p assets/js/{admin,public}
mkdir -p includes/{admin,public,core,api,integrations,database,models,repositories,services,helpers}
mkdir -p includes/admin/{partials,assets}
mkdir -p includes/public/{partials,assets}
mkdir -p docs/{developer,agents,architecture,api,user-guide}
mkdir -p languages
mkdir -p templates/{admin,public,emails}
mkdir -p tests/{unit,integration,e2e}

# 5. Copiar archivos principales desde templates
echo -e "${BLUE}📄 Creando archivos principales...${NC}"

# Plugin main file
cp .dev-templates/plugin-header.php "${PLUGIN_SLUG}.php"

# Composer
cp .dev-templates/composer.json composer.json

# Package.json
cp .dev-templates/package.json package.json

# README
cp .dev-templates/README-wordpress.txt readme.txt

# LICENSE
cp .dev-templates/LICENSE.txt LICENSE.txt

# Knowledge Base (IMPORTANTE - contiene 12+ errores aprendidos)
cp .dev-templates/KNOWLEDGE_BASE.md docs/KNOWLEDGE_BASE.md

# Project Staff (CTO + 10 agentes élite con checklists de activación)
cp .dev-templates/PROJECT_STAFF.md docs/agents/PROJECT_STAFF.md

# Pragmatic Solutions (Identidad de empresa)
cp .dev-templates/PRAGMATIC_SOLUTIONS.md docs/PRAGMATIC_SOLUTIONS.md

# Developer Profile
cp .dev-templates/../docs/developer/DEVELOPER_PROFILE.md docs/developer/DEVELOPER_PROFILE.md

# 6. Reemplazar placeholders
echo -e "${BLUE}🔄 Reemplazando placeholders...${NC}"

# Función para reemplazar en archivo
replace_in_file() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' "$1" "$2"
    else
        # Linux
        sed -i "$1" "$2"
    fi
}

# Lista de archivos a procesar
FILES_TO_REPLACE=(
    "${PLUGIN_SLUG}.php"
    "composer.json"
    "package.json"
    "readme.txt"
)

for file in "${FILES_TO_REPLACE[@]}"; do
    if [ -f "$file" ]; then
        replace_in_file "s/\[PLUGIN_NAME\]/${PLUGIN_NAME}/g" "$file"
        replace_in_file "s/\[PLUGIN NAME\]/${PLUGIN_NAME}/g" "$file"
        replace_in_file "s/\[plugin-slug\]/${PLUGIN_SLUG}/g" "$file"
        replace_in_file "s/\[Plugin_Package\]/${PLUGIN_PACKAGE}/g" "$file"
        replace_in_file "s/\[plugin_function_prefix\]/${PLUGIN_PREFIX}/g" "$file"
        replace_in_file "s/\[PLUGIN_PREFIX\]/${PLUGIN_PREFIX_CONST}/g" "$file"
        replace_in_file "s/\[PLUGIN_DESCRIPTION\]/${PLUGIN_DESCRIPTION}/g" "$file"
        replace_in_file "s/\[Plugin Description\]/${PLUGIN_DESCRIPTION}/g" "$file"
        replace_in_file "s/plugin-name/${PLUGIN_SLUG}/g" "$file"
    fi
done

# 7. Actualizar KNOWLEDGE_BASE.md con nuevo proyecto
echo -e "${BLUE}📚 Registrando nuevo proyecto en Knowledge Base...${NC}"

CURRENT_DATE=$(date +"%B %Y")

cat >> docs/KNOWLEDGE_BASE.md << EOF

---

### Proyecto #X: ${PLUGIN_NAME}
**Inicio**: ${CURRENT_DATE}
**Estado**: En Desarrollo
**Descripción**: ${PLUGIN_DESCRIPTION}
**Stack**: WordPress 6.4+, WooCommerce 8.5+, PHP 8.2+

**Progreso**:
- [ ] Estructura inicial creada
- [ ] Funcionalidad core implementada
- [ ] Testing completado
- [ ] Documentación finalizada
- [ ] Deployment realizado

**Notas**:
_Completar al finalizar el proyecto_

EOF

# 8. Crear .gitignore
echo -e "${BLUE}📝 Creando .gitignore...${NC}"

cat > .gitignore << 'EOF'
# WordPress
wp-config.php
wp-content/uploads/
wp-content/cache/

# Plugin
/vendor/
/node_modules/
/build/
/dist/
*.zip

# IDE
/.idea/
/.vscode/
*.sublime-project
*.sublime-workspace

# OS
.DS_Store
Thumbs.db

# Logs
*.log
error_log
debug.log

# Temporary
*.tmp
*.temp
*.swp
EOF

# 9. Crear README.md
echo -e "${BLUE}📖 Creando README.md...${NC}"

cat > README.md << EOF
# ${PLUGIN_NAME}

${PLUGIN_DESCRIPTION}

## 🚀 Características

- Feature 1
- Feature 2
- Feature 3

## 📋 Requisitos

- WordPress 5.8+
- PHP 7.4+
- WooCommerce 6.0+ (si aplica)

## 🔧 Instalación

1. Descargar el plugin
2. Subir a \`/wp-content/plugins/${PLUGIN_SLUG}\`
3. Activar desde el panel de WordPress
4. Configurar en Settings > ${PLUGIN_NAME}

## 📚 Documentación

Ver documentación completa en: https://cristianfarfan.com.ar/${PLUGIN_SLUG}

## 🐛 Reporte de Bugs

https://github.com/cdfarfan1/${PLUGIN_SLUG}/issues

## 👨‍💻 Desarrollador

**Cristian Farfan**
- 🌐 Web: https://cristianfarfan.com.ar
- 📧 Email: farfancris@gmail.com
- 💼 LinkedIn: [cristianfarfan](https://www.linkedin.com/in/cristianfarfan/)
- 🐙 GitHub: [@cdfarfan1](https://github.com/cdfarfan1)

## 💖 Donaciones

Si este plugin te resulta útil, considera apoyar su desarrollo:
- **PayPal**: [Donar](https://www.paypal.me/cdfdevs)
- **Mercado Pago**: Alias \`cdf.ml\`

## 📄 Licencia

MIT License - ver [LICENSE.txt](LICENSE.txt)

---

Hecho con ❤️ en Argentina
EOF

# 10. Crear CHANGELOG.md
echo -e "${BLUE}📋 Creando CHANGELOG.md...${NC}"

cat > CHANGELOG.md << EOF
# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [Unreleased]

### Added
- Estructura inicial del plugin
- Sistema de conocimiento acumulativo integrado

## [1.0.0] - ${CURRENT_DATE}

### Added
- Release inicial
- ${PLUGIN_DESCRIPTION}

---

**Convenciones**:
- \`Added\` para nuevas características
- \`Changed\` para cambios en funcionalidad existente
- \`Deprecated\` para características que serán removidas
- \`Removed\` para características removidas
- \`Fixed\` para corrección de bugs
- \`Security\` para vulnerabilidades
EOF

# 11. Inicializar Git
echo -e "${BLUE}🔧 Inicializando repositorio Git...${NC}"

git init
git add .
git commit -m "feat: Initial plugin structure for ${PLUGIN_NAME}

- Created complete folder structure
- Integrated knowledge base from previous projects
- Set up development environment
- Added MIT license
- Configured package managers (composer, npm)

Generated with template system by Cristian Farfan
"

# 12. Resumen final
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ Plugin Creado Exitosamente!                               ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}📁 Ubicación:${NC} ${NEW_PLUGIN_DIR}"
echo -e "${YELLOW}📦 Plugin:${NC} ${PLUGIN_NAME}"
echo -e "${YELLOW}🔧 Slug:${NC} ${PLUGIN_SLUG}"
echo ""
echo -e "${BLUE}📚 Próximos Pasos:${NC}"
echo ""
echo "1. cd ${NEW_PLUGIN_DIR}"
echo "2. composer install"
echo "3. npm install"
echo "4. Revisar docs/PRAGMATIC_SOLUTIONS.md (identidad de empresa)"
echo "5. Revisar docs/KNOWLEDGE_BASE.md (12+ errores aprendidos de proyectos anteriores)"
echo "6. Revisar docs/agents/PROJECT_STAFF.md (CTO + 10 agentes élite con checklists)"
echo "7. Implementar funcionalidad siguiendo la arquitectura en .dev-templates/"
echo "8. Al finalizar, actualizar KNOWLEDGE_BASE.md con lecciones aprendidas"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANTE - Pragmatic Solutions Knowledge System:${NC}"
echo "   - Lee docs/PRAGMATIC_SOLUTIONS.md para entender la empresa"
echo "   - Lee docs/KNOWLEDGE_BASE.md COMPLETO antes de empezar"
echo "   - Lee docs/agents/PROJECT_STAFF.md para entender jerarquía CTO > PM > Agentes"
echo "   - Cada agente tiene CHECKLIST PRE-ACTIVACIÓN específico"
echo "   - Aplica las lecciones de los 12+ errores documentados (E001-E012)"
echo "   - Documenta nuevos errores en KNOWLEDGE_BASE.md con formato E[número]"
echo "   - Al terminar, copia archivos actualizados de vuelta a .dev-templates/"
echo ""
echo -e "${GREEN}🚀 ¡Feliz Desarrollo!${NC}"
echo ""
