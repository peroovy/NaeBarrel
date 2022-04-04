INSERT INTO qualities (name) VALUES ('common');
INSERT INTO qualities (name) VALUES ('uncommon');

INSERT INTO transaction_types (name) VALUES ('Продажа предмета');
INSERT INTO transaction_types (name) VALUES ('Покупка кейса');
INSERT INTO transaction_types (name) VALUES ('Ежедневное получение');

INSERT INTO permissions (name) VALUES ('admin');
INSERT INTO permissions (name) VALUES ('moderator');
INSERT INTO permissions (name) VALUES ('user');

INSERT INTO clients (login, password, email, permission, balance) VALUES ('nikitasamkov', '123', 'nick.samkov@yandex.ru', 1, 500);
INSERT INTO clients (login, password, email, permission, balance) VALUES ('matvey', '007', null, 2, 500);
INSERT INTO clients (login, password, email, permission, balance) VALUES ('thetmgaming', '228', 'iurik.pro@nikita.style', 3, 100);

INSERT INTO transactions (type, client_id, accrual) VALUES (1, 1, 150);
INSERT INTO transactions (type, client_id, accrual) VALUES (3, 1, -150);
INSERT INTO transactions (type, client_id, accrual) VALUES (2, 2, 500);
INSERT INTO transactions (type, client_id, accrual) VALUES (2, 3, -300);

INSERT INTO cases (name, description, price, picture) VALUES ('TestCase', 'Case for tests', 100, 'bruh');
INSERT INTO cases (name, description, price, picture) VALUES ('SecondCase', 'description', 150, 'brah');

INSERT INTO items (name, description, price, quality, picture) VALUES ('test item', 'for tests', 5, 1, 'bruh x2');
INSERT INTO items (name, description, price, quality, picture) VALUES ('second item', 'abc', 15, 1, 'sus');
INSERT INTO items (name, description, price, quality, picture) VALUES ('item #3', 'omg', 150, 1, 'picture :)');
INSERT INTO items (name, description, price, quality, picture) VALUES ('4', '4 item', 1337, 1, 'wtf');
INSERT INTO items (name, description, price, quality, picture) VALUES ('magical crown', 'wow', 100500, 2, 'pretty');

INSERT INTO case_item (case_id, item_id, chance) VALUES (1, 1, 0.5);
INSERT INTO case_item (case_id, item_id, chance) VALUES (1, 2, 0.25);
INSERT INTO case_item (case_id, item_id, chance) VALUES (1, 3, 0.25);
INSERT INTO case_item (case_id, item_id, chance) VALUES (2, 1, 0.1);
INSERT INTO case_item (case_id, item_id, chance) VALUES (2, 4, 0.9);

INSERT INTO inventories (client_id, item_id) VALUES (1, 1);
INSERT INTO inventories (client_id, item_id) VALUES (1, 2);
INSERT INTO inventories (client_id, item_id) VALUES (1, 3);
INSERT INTO inventories (client_id, item_id) VALUES (3, 4);
INSERT INTO inventories (client_id, item_id) VALUES (3, 5);
