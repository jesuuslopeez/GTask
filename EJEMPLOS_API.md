# Ejemplos de peticiones (curl y httpie)

## Base URL
- API: http://localhost:8080/api
- Las sesiones se guardan en cookies, asi que hay que reutilizarlas.

## Registro
### curl
```bash
curl -i -X POST http://localhost:8080/api/register \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ana","email":"ana@example.com","password":"secreto"}' \
  -c cookies.txt
```

### httpie
```bash
http --session=gtask POST http://localhost:8080/api/register \
  name=Ana email=ana@example.com password=secreto
```

## Login
### curl
```bash
curl -i -X POST http://localhost:8080/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"ana@example.com","password":"secreto"}' \
  -c cookies.txt
```

### httpie
```bash
http --session=gtask POST http://localhost:8080/api/login \
  email=ana@example.com password=secreto
```

## Usuario actual
### curl
```bash
curl -i http://localhost:8080/api/me -b cookies.txt
```

### httpie
```bash
http --session=gtask GET http://localhost:8080/api/me
```

## Crear tarea
### curl
```bash
curl -i -X POST http://localhost:8080/api/tasks \
  -H 'Content-Type: application/json' \
  -d '{"title":"Primera","description":"Demo","status":"pending","priority":1}' \
  -b cookies.txt
```

### httpie
```bash
http --session=gtask POST http://localhost:8080/api/tasks \
  title=Primera description=Demo status=pending priority:=1
```

## Listar tareas
### curl
```bash
curl -i http://localhost:8080/api/tasks -b cookies.txt
```

### httpie
```bash
http --session=gtask GET http://localhost:8080/api/tasks
```

## Ver una tarea
### curl
```bash
curl -i http://localhost:8080/api/tasks/1 -b cookies.txt
```

### httpie
```bash
http --session=gtask GET http://localhost:8080/api/tasks/1
```

## Actualizar tarea (PATCH)
### curl
```bash
curl -i -X PATCH http://localhost:8080/api/tasks/1 \
  -H 'Content-Type: application/json' \
  -d '{"status":"completed"}' \
  -b cookies.txt
```

### httpie
```bash
http --session=gtask PATCH http://localhost:8080/api/tasks/1 status=completed
```

## Eliminar tarea
### curl
```bash
curl -i -X DELETE http://localhost:8080/api/tasks/1 -b cookies.txt
```

### httpie
```bash
http --session=gtask DELETE http://localhost:8080/api/tasks/1
```

## Logout
### curl
```bash
curl -i -X POST http://localhost:8080/api/logout -b cookies.txt
```

### httpie
```bash
http --session=gtask POST http://localhost:8080/api/logout
```
