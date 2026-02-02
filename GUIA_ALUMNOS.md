# Guia de estudio para el proyecto GTask

Objetivo: revisar la app y contestar preguntas sobre su estructura, conexion a BD, entrada/salida de datos y seguridad.

## 1) Estructura del proyecto
- Localiza las carpetas principales y explica para que sirve cada una.
- Indica donde se encuentran: configuracion, controladores, vistas, assets y scripts.

Preguntas:
- ¿Que archivo es el punto de entrada cuando llega una peticion?
- ¿Donde se configuran los datos de conexion a la base de datos?
- ¿Que archivos contienen la logica de autenticacion y tareas?

## 2) Flujo de una peticion
- Dibuja el recorrido desde que llega a Nginx hasta que vuelve la respuesta.

Preguntas:
- ¿Como se decide si la peticion es una ruta de API?
- ¿Que ocurre cuando la ruta es / (raiz)?

## 3) Datos que envia el cliente
- Identifica como se lee el JSON que manda el cliente.
- Explica que estructura debe tener el JSON para crear una tarea.

Preguntas:
- ¿Que funcion lee el cuerpo de la peticion?
- ¿Que valida antes de insertar en la BD?

## 4) Conexion a la base de datos
- Explica como se construye el DSN y que datos necesita.
- Indica donde se crea el objeto PDO y como se reutiliza.

Preguntas:
- ¿De donde salen DB_HOST, DB_PORT, etc.?
- ¿Que opciones de PDO estan activadas?

## 5) Autenticacion y sesiones
- Explica como se guarda el usuario autenticado.
- Indica que rutas requieren estar autenticado.

Preguntas:
- ¿Que funcion comprueba si hay sesion?
- ¿Que datos se guardan en la sesion del usuario?

## 6) Seguridad basica
- Explica como se evita SQL injection.
- Localiza las validaciones de entrada.

Preguntas:
- ¿Que es una consulta preparada?
- ¿Que validaciones se aplican a titulo, email y fecha?

## 7) CORS
- Busca donde se configuran las cabeceras CORS.

Preguntas:
- ¿Como se habilita/deshabilita CORS?
- ¿Que ocurre con una peticion OPTIONS?

## 8) Practica con la API
- Usa `curl` o `httpie` para probar: registro, login, crear tarea, listar tareas.
- Anota respuestas y errores.

Entrega
- Respuestas a las preguntas.
- Capturas o comandos usados en las pruebas.
