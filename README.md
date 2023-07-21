# Estate Agency Platform Example

## Installation
```bash
git clone git@github.com:weidali/full-stack.git
cd full-stack
```

Set the DB connection credantionals into config file
```bash
php -S localhost:8080
```

## Screenshots
![Preview 1](https://github.com/weidali/full-stack/assets/16261185/99294b90-dfa2-4692-8ad2-95e0865af5fe "Example of users page")

<hr>

![80%](https://progress-bar.dev/80/?title=progress)

Completed tasks 8/10
```txt
+ таблица Users с владельцами участков (колонки Plot ID, First name, Last Name, Phone, Email, Last login)
+ пагинация по 20 записей на страницу (аналогично таблице Plots)
+ поиск по номеру телефона, имени и email пользователя
+ страница реализуется в схожем дизайне, как страница с Plots
+ возможность создания/редактирования пользователя (поля First name, Last name, Phone, Email, Plots)
+ должна поддерживаться возможность добавить пользователя сразу к нескольким участкам (через запятую в поле Plots)
- если при редактировании какие-либо поля, кроме Plots не заполнены, не давать сохранить данные
- при сохранении данных телефон фильтруется по нечисловым символам, email переводится в lower case
+ в меню при выборе раздела Users он должен подсвечиваться аналогично выбору Plots
+ возможность удаления пользователя
```
