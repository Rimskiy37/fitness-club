# FitLife — Система управления фитнес-клубом

## Описание

Информационная система для управления фитнес-клубом (индивидуальное задание №15).

**Цель:** Повышение удержания клиентов и автоматизация контроля посещаемости.

**Аудитория:** Члены клуба, тренеры, администраторы, менеджер.

## Технологии

| Слой | Технология |
|------|-----------|
| Backend | PHP 8.x (ООП) |
| Frontend | HTML5 / CSS3 / JS (Fetch API) |
| База данных | MySQL 8.0 / MariaDB 10.4+ |
| Сервер | PHP Built-in (`php -S`) |

## Быстрый старт

### Что нужно установить

1. **PHP 8.0+** — [windows.php.net/download](https://windows.php.net/download/) (Zip TS x64)
2. **MySQL** — входит в состав XAMPP или [dev.mysql.com](https://dev.mysql.com/downloads/)

### Установка за 3 шага

**Шаг 1 — Настройка БД** (одна команда):
```cmd
php setup.php
```
Если MySQL с паролем:
```cmd
php setup.php "твой_пароль"
```

**Шаг 2 — Запуск сервера:**
```cmd
php -S localhost:8000 router.php
```

**Шаг 3 — Открыть в браузере:**
```
http://localhost:8000
```

### Тестовые аккаунты

Пароль для всех: **password123**

| Роль | Email |
|------|-------|
| Администратор | admin@fitnessclub.ru |
| Менеджер | manager@fitnessclub.ru |
| Тренер | trainer1@fitnessclub.ru |
| Клиент | member1@mail.ru |

**Админ-панель:** http://localhost:8000/admin

## Структура проекта

```
fitness-club/
├── router.php                # Роутер (точка входа)
├── setup.php                 # Скрипт создания БД
├── docs/                     # Документация
├── database/                 # SQL-файлы (схема, данные, запросы)
├── public/                   # Публичные страницы + API + assets
├── src/                      # PHP-классы, модели, контроллеры
│   ├── classes/              # ООП-сущности
│   ├── models/               # Работа с БД
│   ├── controllers/          # Контроллеры
│   └── helpers/              # Конфигурация
└── admin/                    # Административная панель
```

## Страницы

| URL | Описание |
|-----|----------|
| `/` | Главная страница |
| `/classes` | Расписание занятий |
| `/login` | Вход |
| `/register` | Регистрация |
| `/profile` | Личный кабинет |
| `/admin` | Админ-панель (дашборд) |
| `/admin/classes` | Управление занятиями |
| `/admin/bookings` | Управление записями |
| `/admin/members` | Клиенты |
| `/admin/memberships` | Клубные карты |
| `/admin/visits` | Посещения |

## AJAX (7 действий без перезагрузки)

1. Поиск занятий — `GET /api/search-classes.php?q=йога`
2. Фильтрация по категории — `GET /api/filter-classes.php?category=yoga`
3. Запись на занятие — `POST /api/book-class.php`
4. Отмена записи — `POST /api/cancel-booking.php`
5. Проверка email — `GET /api/check-email.php?email=test@mail.ru`
6. Статус записи (админ) — `POST /admin/api/update-booking-status.php`
7. Статистика (админ) — `GET /admin/api/stats.php`
