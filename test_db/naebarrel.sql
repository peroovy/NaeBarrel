INSERT INTO public.qualities (id, name) VALUES (3, 'common');
INSERT INTO public.qualities (id, name) VALUES (4, 'uncommon');

INSERT INTO public.transaction_types (id, name) VALUES (1, 'Продажа предмета');
INSERT INTO public.transaction_types (id, name) VALUES (3, 'Покупка кейса');
INSERT INTO public.transaction_types (id, name) VALUES (2, 'Ежедневное получение');

INSERT INTO public.permissions (id, name) VALUES (1, 'admin');
INSERT INTO public.permissions (id, name) VALUES (2, 'moderator');
INSERT INTO public.permissions (id, name) VALUES (3, 'user');

INSERT INTO public.clients (id, login, password, email, permission, balance) VALUES (1, 'nikitasamkov', '123', 'nick.samkov@yandex.ru', 1, 500);

INSERT INTO public.transactions (id, type, client_id, accrual) VALUES (1, 1, 1, 150);
INSERT INTO public.transactions (id, type, client_id, accrual) VALUES (2, 3, 1, -150);
INSERT INTO public.transactions (id, type, client_id, accrual) VALUES (3, 2, 1, 500);
INSERT INTO public.transactions (id, type, client_id, accrual) VALUES (4, 2, 1, -300);

INSERT INTO public.cases (id, name, description, price, picture) VALUES (1, 'TestCase', 'Case for tests', 100, 'bruh');
INSERT INTO public.cases (id, name, description, price, picture) VALUES (2, 'SecondCase', 'description', 150, 'brah');

INSERT INTO public.items (id, name, description, price, quality, picture) VALUES (1, 'test item', 'for tests', 5, 3, 'bruh x2');
INSERT INTO public.items (id, name, description, price, quality, picture) VALUES (2, 'second item', 'abc', 15, 3, 'sus');
INSERT INTO public.items (id, name, description, price, quality, picture) VALUES (3, 'item #3', 'omg', 150, 3, 'picture :)');
INSERT INTO public.items (id, name, description, price, quality, picture) VALUES (4, '4', '4 item', 1337, 3, 'wtf');
INSERT INTO public.items (id, name, description, price, quality, picture) VALUES (5, 'magical crown', 'wow', 100500, 4, 'pretty');

INSERT INTO public.case_item (case_id, item_id, chance) VALUES (1, 1, 0.5);
INSERT INTO public.case_item (case_id, item_id, chance) VALUES (1, 2, 0.25);
INSERT INTO public.case_item (case_id, item_id, chance) VALUES (1, 3, 0.25);
INSERT INTO public.case_item (case_id, item_id, chance) VALUES (2, 1, 0.1);
INSERT INTO public.case_item (case_id, item_id, chance) VALUES (2, 4, 0.9);