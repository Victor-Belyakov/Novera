# Настройка Cron для команд привычек

## Команды для настройки

1. **`app:habits:generate-logs`** - создание pending-логов для активных привычек (запускать ежедневно в 00:00)
2. **`app:habits:mark-skipped`** - пометка просроченных pending-логов как skipped (запускать ежедневно в 00:05)
3. **`app:reminders:send-due-emails`** - отправка e-mail по напоминаниям, у которых `notify_at` уже наступило (запускать каждые 1–5 минут или по cron; перед этим настройте `MAILER_DSN` и `MAILER_FROM` в `.env`)

### E-mail напоминания

В `.env` задайте реальный транспорт, например:

```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM="Novera <noreply@yourdomain.com>"
```

Для разработки можно оставить `null://null` (письма никуда не уходят, ошибок не будет).

Пример cron (каждые 5 минут):

```cron
*/5 * * * * cd /path/to/Novera && docker exec novera_php php bin/console app:reminders:send-due-emails >> /var/log/novera-reminders.log 2>&1
```

## Вариант 1: Cron на хосте (рекомендуется)

Настройте cron на хосте для выполнения команд через Docker:

```bash
# Открыть crontab для редактирования
crontab -e

# Добавить следующие строки:
# Генерация логов привычек каждый день в 00:00
0 0 * * * cd /home/victor/Документы/Projects/Novera && docker exec novera_php php bin/console app:habits:generate-logs >> /var/log/novera-habits.log 2>&1

# Пометка пропущенных логов каждый день в 00:05
5 0 * * * cd /home/victor/Документы/Projects/Novera && docker exec novera_php php bin/console app:habits:mark-skipped >> /var/log/novera-habits.log 2>&1
```

**Важно:** Замените путь `/home/victor/Документы/Projects/Novera` на актуальный путь к вашему проекту.

## Вариант 2: Cron внутри PHP контейнера

Если хотите запускать cron внутри контейнера:

### 1. Создайте файл crontab внутри контейнера

```bash
# Войти в PHP контейнер
docker exec -it novera_php bash

# Создать файл crontab
cat > /tmp/crontab << EOF
# Генерация логов привычек каждый день в 00:00
0 0 * * * cd /var/www/html && php bin/console app:habits:generate-logs >> /var/log/habits.log 2>&1

# Пометка пропущенных логов каждый день в 00:05
5 0 * * * cd /var/www/html && php bin/console app:habits:mark-skipped >> /var/log/habits.log 2>&1
EOF

# Установить crontab
crontab /tmp/crontab
```

### 2. Убедитесь, что cron запущен в контейнере

Добавьте в Dockerfile PHP контейнера или запустите cron вручную:

```bash
# В контейнере
crond -f -d 8
```

### 3. Или используйте docker-compose с cron

Создайте отдельный сервис для cron в `docker-compose.yml`:

```yaml
cron:
  build:
    context: ./backend
    dockerfile: docker/php/Dockerfile
  container_name: novera_cron
  volumes:
    - ./backend:/var/www/html:rw
  working_dir: /var/www/html
  depends_on:
    - database
    - php
  networks:
    - novera_network
  environment:
    - DATABASE_URL=postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@database:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8
  command: >
    sh -c "
    echo '0 0 * * * cd /var/www/html && php bin/console app:habits:generate-logs >> /var/log/habits.log 2>&1' > /tmp/crontab &&
    echo '5 0 * * * cd /var/www/html && php bin/console app:habits:mark-skipped >> /var/log/habits.log 2>&1' >> /tmp/crontab &&
    crontab /tmp/crontab &&
    crond -f -d 8
    "
```

## Вариант 3: Использование systemd timer (Linux)

Создайте два systemd timer файла:

### `/etc/systemd/system/novera-generate-logs.service`
```ini
[Unit]
Description=Generate habit logs
After=docker.service
Requires=docker.service

[Service]
Type=oneshot
ExecStart=/usr/bin/docker exec novera_php php /var/www/html/bin/console app:habits:generate-logs
```

### `/etc/systemd/system/novera-generate-logs.timer`
```ini
[Unit]
Description=Run generate habit logs daily
Requires=novera-generate-logs.service

[Timer]
OnCalendar=daily
OnCalendar=00:00

[Install]
WantedBy=timers.target
```

### `/etc/systemd/system/novera-mark-skipped.service`
```ini
[Unit]
Description=Mark skipped habit logs
After=docker.service
Requires=docker.service

[Service]
Type=oneshot
ExecStart=/usr/bin/docker exec novera_php php /var/www/html/bin/console app:habits:mark-skipped
```

### `/etc/systemd/system/novera-mark-skipped.timer`
```ini
[Unit]
Description=Run mark skipped habit logs daily
Requires=novera-mark-skipped.service

[Timer]
OnCalendar=daily
OnCalendar=00:05

[Install]
WantedBy=timers.target
```

Затем активируйте таймеры:
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now novera-generate-logs.timer
sudo systemctl enable --now novera-mark-skipped.timer
```

## Проверка работы

### Проверить cron на хосте:
```bash
# Просмотреть текущие задачи cron
crontab -l

# Проверить логи cron
grep CRON /var/log/syslog
# или
journalctl -u cron
```

### Проверить выполнение команд вручную:
```bash
# Генерация логов
docker exec novera_php php bin/console app:habits:generate-logs

# Пометка пропущенных
docker exec novera_php php bin/console app:habits:mark-skipped
```

### Проверить логи выполнения:
```bash
# Если настроили логирование в файл
tail -f /var/log/novera-habits.log
```

## Рекомендации

1. **Используйте Вариант 1** (cron на хосте) - это самый простой и надежный способ
2. Убедитесь, что Docker контейнер `novera_php` всегда запущен
3. Проверьте права доступа к лог-файлам
4. Убедитесь, что путь к проекту в crontab правильный
5. Для отладки сначала запустите команды вручную, чтобы убедиться, что они работают

## Пример полной настройки (Вариант 1)

```bash
# 1. Создать директорию для логов (если нужно)
sudo mkdir -p /var/log
sudo touch /var/log/novera-habits.log
sudo chmod 666 /var/log/novera-habits.log

# 2. Открыть crontab
crontab -e

# 3. Добавить строки (замените путь на свой):
0 0 * * * cd /home/victor/Документы/Projects/Novera && docker exec novera_php php bin/console app:habits:generate-logs >> /var/log/novera-habits.log 2>&1
5 0 * * * cd /home/victor/Документы/Projects/Novera && docker exec novera_php php bin/console app:habits:mark-skipped >> /var/log/novera-habits.log 2>&1

# 4. Сохранить и выйти

# 5. Проверить, что задачи добавлены
crontab -l
```
