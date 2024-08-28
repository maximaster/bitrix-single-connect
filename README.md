# maximaster/bitrix-single-connect

В старых версиях Битрикс каждый запуск ядра делает два подключения к базе
данных - старое и новое (D7).

Данный пакет делает возможным проведение всех запросов к базе данных через
D7-соединение.

```bash
composer require maximaster/bitrix-single-connect
```

**init.php**

```php
// Заменяет $GLOBALS['DB'] на собственную реализацию,
// которая обращается к D7 соединению.
ProxyConnection::replace();
```
