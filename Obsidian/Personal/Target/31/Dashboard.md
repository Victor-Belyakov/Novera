---
type: dashboard
area: target-31
tags:
  - dashboard
  - planning
  - track
---

# Dashboard 31

> [!summary] Главная идея
> Сначала построить устойчивую систему, потом наращивать скорость.

## Compass

> [!info] Приоритеты
> `1.` Режим
> `2.` Деньги
> `3.` Здоровье
> `4.` Go / MMORPG
> `5.` Всё остальное

## Карта
- [[Personal/Target/31/General|Общие цели]]
- [[Personal/Target/31/Moth's/May|План на май]]
- [[Personal/Daily/Schedule|Недельный ритм]]
- [[Personal/Target/31/Board|Канбан-доска]]
- [[Personal/Target/35|Образ жизни в 35]]
- [[Personal/Target/Ideas|Идеи]]

## Треки

| Трек | Статус | Ближайший результат |
| --- | --- | --- |
| Go / MMORPG | `main` | MVP с базовым игровым циклом |
| PHP / Backend | `support` | 1-2 блока в неделю |
| Деньги | `critical` | План по долгам и кредиткам |
| Здоровье | `critical` | Сон, питание, 10 тренировок за май |
| Английский | `support` | Ровный ежедневный минимум |
| Быт | `support` | Снизить хаос и закрыть хвосты |

## Канбан: Сейчас

### Inbox
- [ ] Выписать все долги, суммы и даты
- [ ] Определить MVP для текстовой MMORPG
- [ ] Составить простое меню на неделю
- [ ] Собрать список покупок по гардеробу и быту

### In Progress
- [ ] Держать сон до `22:30`
- [ ] Ходить в зал 3 раза в неделю
- [ ] Делать короткие занятия английским

### This Week
- [ ] 4 сессии по `Golang / MMORPG`
- [ ] 1 блок по `PHP/backend`
- [ ] 1 финансовый разбор
- [ ] 1 блок на уборку / разбор вещей

### Done
- [x] Закрыть кредитку
- [x] Купить кошелек
- [x] Собрать рабочий недельный ритм

## Live Views

### Активные страницы
```dataview
TABLE type as "Type", status as "Status", review as "Review"
FROM "Personal/Target/31"
WHERE type
SORT file.name ASC
```

### Открытые задачи по целям
```dataview
TASK
FROM "Personal/Target/31"
WHERE !completed
GROUP BY file.link
```

### Что уже закрыто
```dataview
TASK
FROM "Personal/Target/31"
WHERE completed
GROUP BY file.link
```

## Quick Win
- [ ] Подготовить одежду и еду на 3 дня вперёд
- [ ] Записать 3 ближайшие задачи по `Go`
- [ ] Убрать один визуальный завал дома

## Правила
- Не тащить больше одного главного проекта одновременно
- Плохой день не отменяет неделю
- Сначала режим и деньги, потом всё остальное
