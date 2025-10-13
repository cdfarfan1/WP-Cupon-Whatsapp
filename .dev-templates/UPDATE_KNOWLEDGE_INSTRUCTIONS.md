# 📚 INSTRUCCIONES PARA ACTUALIZAR BASE DE CONOCIMIENTO

## 🎯 Propósito

Este sistema permite que **cada proyecto acumule conocimiento** y lo transmita a futuros proyectos. Es como tener un "segundo cerebro" que crece con cada plugin.

---

## 🔄 FLUJO DE TRABAJO

### 📥 AL INICIAR UN NUEVO PROYECTO

```bash
# 1. Ejecutar script de copia
cd /ruta/al/proyecto/actual/.dev-templates/
bash copy-to-new-project.sh

# 2. El script hará:
✅ Copiar toda la carpeta .dev-templates/
✅ Crear estructura completa de carpetas
✅ Copiar KNOWLEDGE_BASE.md con TODO el conocimiento previo
✅ Registrar el nuevo proyecto en el historial
✅ Inicializar Git con commit inicial

# 3. LEER obligatoriamente:
docs/KNOWLEDGE_BASE.md

# Este archivo contiene:
- Errores de proyectos anteriores (para NO repetirlos)
- Soluciones exitosas (para reutilizarlas)
- Snippets de código probados
- Patrones de diseño que funcionaron
- Integraciones ya dominadas
```

---

### 📤 AL TERMINAR UN PROYECTO

```bash
# 1. Abrir docs/KNOWLEDGE_BASE.md

# 2. ACTUALIZAR estas secciones:

## ❌ Errores Aprendidos
# Agregar TODOS los errores nuevos que encontraste
# Formato:
### E[número] - Título
**Proyecto**: [Nombre]
**Fecha**: [Fecha]
**Problema**: [Qué pasó]
**Causa Raíz**: [Por qué pasó]
**Solución**: [Cómo se resolvió]
**Prevención**: [Cómo evitarlo en el futuro]

## ✅ Soluciones Exitosas
# Agregar patrones/soluciones que funcionaron MUY bien
### S[número] - Título
**Descripción**: ...
**Reutilizable**: ✅ Sí / ❌ No

## 📦 Snippets Reutilizables
# Agregar código que puedas copiar/pegar en futuros proyectos

## 📊 Historial de Proyectos
# Completar la entrada del proyecto actual
### Proyecto #X: [Nombre]
**Finalización**: [Fecha]
**Logros**: ...
**Errores Aprendidos**: X
**Líneas de Código**: X

# 3. Actualizar contador arriba del documento:
**Proyectos Completados**: X → X+1

# 4. COPIAR el archivo actualizado de vuelta a templates:
cp docs/KNOWLEDGE_BASE.md .dev-templates/KNOWLEDGE_BASE.md

# 5. Commit:
git add .dev-templates/KNOWLEDGE_BASE.md
git commit -m "docs: Update knowledge base with project learnings"
git push
```

---

## 📋 TEMPLATE PARA REGISTRAR ERRORES

Copia y pega esto cada vez que encuentres un error:

```markdown
### E[número siguiente] - [Título Descriptivo]
**Proyecto**: [Nombre del Plugin]
**Fecha**: [Fecha]
**Problema**: [Descripción clara del problema]
**Causa Raíz**: [Por qué ocurrió - análisis profundo]
**Solución**: [Cómo lo resolviste paso a paso]
**Prevención**: [Qué hacer para evitarlo en futuros proyectos]
**Código de Ejemplo**:
```php
// Código que demuestra la solución
```
```

---

## 📋 TEMPLATE PARA REGISTRAR SOLUCIONES

```markdown
### S[número siguiente] - [Título de la Solución]
**Proyecto**: [Nombre del Plugin]
**Descripción**: [Qué hace esta solución]
**Resultado**: [Qué logró]
**Código**: Ver `ruta/al/archivo.php`
**Reutilizable**: ✅ Sí / ❌ No

**Implementación**:
1. Paso 1
2. Paso 2
3. Paso 3

**Código**:
```php
// Código reutilizable aquí
```
```

---

## 📋 TEMPLATE PARA REGISTRAR PROYECTO COMPLETADO

```markdown
### Proyecto #X: [Nombre del Plugin]
**Inicio**: [Mes Año]
**Finalización**: [Mes Año]
**Versión Final**: X.X.X
**Descripción**: [Descripción breve]
**Stack**: WordPress X.X, WooCommerce X.X, PHP X.X

**Logros**:
- Logro 1
- Logro 2
- Logro 3

**Errores Aprendidos**: X (E001 - E0XX)
**Soluciones Creadas**: X (S001 - S0XX)
**Líneas de Código**: ~X,XXX
**Tiempo Total**: ~XXX horas

**Documentación Generada**:
- Documento 1
- Documento 2

**Lecciones Clave**:
1. Lección 1
2. Lección 2
3. Lección 3
```

---

## 🎯 CHECKLIST ANTES DE COPIAR A NUEVO PROYECTO

Antes de ejecutar `copy-to-new-project.sh`, verifica:

- [ ] KNOWLEDGE_BASE.md está actualizado con el proyecto actual
- [ ] Todos los errores nuevos están documentados
- [ ] Todas las soluciones exitosas están registradas
- [ ] El historial de proyectos está completo
- [ ] Los snippets útiles fueron agregados
- [ ] El contador de "Proyectos Completados" está actualizado
- [ ] El archivo fue copiado a `.dev-templates/KNOWLEDGE_BASE.md`
- [ ] Hiciste commit de los cambios

---

## 💡 BENEFICIOS DE ESTE SISTEMA

### Para Ti (Cristian):
1. **No repites errores**: Cada error lo cometes UNA VEZ
2. **Desarrollo más rápido**: Reutilizas código probado
3. **Mejor calidad**: Aplicas mejores prácticas desde día 1
4. **Crecimiento constante**: Cada proyecto te hace mejor

### Para el Equipo de Agentes:
1. **Aprendizaje acumulativo**: Los agentes ven errores históricos
2. **Mejores decisiones**: Aplican soluciones probadas
3. **Menos warnings**: Evitan patrones problemáticos
4. **Code review automático**: Comparan contra base de conocimiento

---

## 📊 MÉTRICAS DE ÉXITO

Mide tu mejora en cada proyecto:

| Métrica | Proyecto #1 | Proyecto #2 | Proyecto #3 |
|---------|-------------|-------------|-------------|
| Errores Críticos | 12 | ? | ? |
| Tiempo Debug | 40h | ? | ? |
| Funciones Duplicadas | 4 | ? | ? |
| Tiempo Total | 160h | ? | ? |

**Objetivo**: Reducir errores y tiempo en cada proyecto.

---

## 🚨 IMPORTANTE

### ⚠️ NUNCA olvides:

1. **Leer KNOWLEDGE_BASE.md ANTES de empezar** un nuevo proyecto
2. **Documentar TODOS los errores** que encuentres
3. **Actualizar y copiar** KNOWLEDGE_BASE.md al terminar
4. **Compartir con agentes** - ellos leen este archivo automáticamente

### ✅ Esto te asegura:

- Errores no se repiten
- Conocimiento se acumula
- Cada proyecto es mejor que el anterior
- Tu expertise crece exponencialmente

---

## 📞 RECORDATORIOS

### Al Iniciar Proyecto:
```
📚 ¿Leíste KNOWLEDGE_BASE.md?
📝 ¿Revisaste los 12+ errores previos?
✅ ¿Aplicaste las soluciones exitosas?
```

### Durante Proyecto:
```
📝 ¿Encontraste un error nuevo? → Documentarlo
💡 ¿Resolviste algo elegante? → Agregarlo a soluciones
🔧 ¿Creaste código reutilizable? → Agregarlo a snippets
```

### Al Terminar Proyecto:
```
✅ ¿Actualizaste KNOWLEDGE_BASE.md?
✅ ¿Copiaste a .dev-templates/?
✅ ¿Hiciste commit?
✅ ¿Incrementaste contador de proyectos?
```

---

## 🎯 TU OBJETIVO

**Después de 5 proyectos:**
- Errores críticos: 0
- Tiempo de debug: <5 horas
- Velocidad de desarrollo: 2x más rápido
- Calidad de código: 10/10

**Después de 10 proyectos:**
- Base de conocimiento: 50+ errores documentados
- Snippets reutilizables: 100+
- Patrones probados: 20+
- Eres un experto en WordPress plugins

---

**📅 Creado**: 8 de Octubre, 2025
**✍️ Autor**: Cristian Farfan
**📊 Versión**: 1.0.0

---

**🎯 Recuerda**: *"El conocimiento no se pierde, se acumula."*
