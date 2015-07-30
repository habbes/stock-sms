CREATE TABLE stocks (
id integer primary key,
company text unique,
price decimal
);
CREATE TABLE subscribers (
phone text primary key
);
CREATE TABLE smsinfo (
last_read_time datetime);
CREATE TABLE subscriptions(
id integer primary key,
company text,
phone text,
automatic boolean
);
