use online_store;

-- users --

insert into Users ( User_Name , SSN ,
				First_Name , Last_Name , Sex ,
                Register_Date , Password , Email , password_plain)
values 
	('AhmedSarhan1' , '12345678912345' , 'Ahmed' , 'Sarhan' , 'Male' , '2025-12-04', '12345678' ,'AhmedSarhan@gmail.com' , '1234'),
	('LailaHassan', '98765432198765', 'Laila', 'Hassan', 'Female', '2025-11-30', 'pass1234', 'LailaH@gmail.com', '1234'),
	('AhmedSameh1', '45678912345678', 'Ahmed', 'Sameh', 'Male', '2025-11-28', 'pass1234', 'AhmedSameh@gmail.com', '1234'),
	('SaraKhaled', '32165498732165', 'Sara', 'Khaled', 'Female', '2025-11-25', 'pass1234', 'SaraK@gmail.com', '1234'),
	('Youssef1', '15975348625814', 'Youssef', 'Zaki', 'Male', '2025-11-24', 'pass1234', 'Youssef@gmail.com', '1234'),
	('NourSamir', '85296374125896', 'Nour', 'Samir', 'Female', '2025-11-23', 'pass1234', 'NourS@gmail.com', '1234'),
	('HanyMahmoud', '14725836914785', 'Hany', 'Mahmoud', 'Male', '2025-11-22', 'pass1234', 'HanyM@gmail.com', '1234'),
	('MonaYasser', '36925814796385', 'Mona', 'Yasser', 'Female', '2025-11-20', 'pass1234', 'MonaY@gmail.com', '1234'),
	('TamerHossam', '75315948632147', 'Tamer', 'Hossam', 'Male', '2025-11-19', 'pass1234', 'TamerH@gmail.com', '1234'),
	('DinaNabil', '95175348625847', 'Dina', 'Nabil', 'Female', '2025-11-18', 'pass1234', 'DinaN@gmail.com', '1234'),
	('KhaledAdel', '25814796385214', 'Khaled', 'Adel', 'Male', '2025-11-17', 'pass1234', 'KhaledA@gmail.com', '1234'),
	('FatmaHany', '65432198765432', 'Fatma', 'Hany', 'Female', '2025-11-15', 'pass1234', 'FatmaH@gmail.com', '1234'),
	('YoussefTarek', '32198765432198', 'Youssef', 'Tarek', 'Male', '2025-11-14', 'pass1234', 'YoussefT@gmail.com', '1234');
        
select * from Users;


-- Admin --

insert into Administrators (Admin_ID)
values (1);

select * from Administrators;

select * 
	from Administrators ad
	inner join Users u
	on Ad.Admin_ID = U.User_ID;


-- agent--

insert into Agents (Agent_ID , Office)
values 
	(3 , 1),
    (5 , 2);

select * from Agents;

select *
	from Agents ag
	inner join Users u
	on ag.Agent_ID = u.User_ID;


-- Customer --

insert into Customers (Customer_ID , Points)
values
	(2 , 1200) , (4 , 3400) , (6 , 800) , (7 , 1700) , (8 , 2400),
	(9 , 2000) , (10 , 1350) , (11 , 3310) , (12 , 5000) , (13 , 4860);
    
select * from Customers;

select *
	from Customers c
	inner join Users u
	on c.Customer_ID = u.User_ID;


-- Category --

INSERT INTO Categories (Admin_ID, Category_Name, Description)
VALUES 
	(1, 'Furniture', 'Various types of home and office furniture.'),
	(1, 'Electronics', 'Electronic devices including computers, phones, and appliances.'),
	(1, 'Dairy', 'Milk, cheese, yogurt, and other dairy products.');

select* from Categories;

select * from Categories c
	inner join Administrators ad 
	on ad.Admin_ID = C.Admin_ID
	inner join Users u on u.User_ID = C.Admin_ID;


-- Product --

INSERT INTO Products (Product_Name, Current_Price, Stock, Description, Admin_ID, Category_Name)
VALUES
	('Water 1L', 10, 300, 'Pure drinking water, 1 liter bottle.', 1, 'Dairy'),
	('Milk 1L', 15, 200, 'Fresh cow milk, 1 liter carton.', 1, 'Dairy'),
	('Cheese Block', 50, 150, 'High-quality cheddar cheese block, 500g.', 1, 'Dairy'),
	('Laptop', 5000, 50, '15-inch laptop with 16GB RAM and 512GB SSD.', 1, 'Electronics'),
	('Smartphone', 3000, 100, 'Latest model smartphone with high-resolution camera.', 1, 'Electronics'),
	('Headphones', 250, 80, 'Noise-cancelling over-ear headphones.', 1, 'Electronics'),
	('Office Chair', 800, 40, 'Ergonomic office chair with adjustable height.', 1, 'Furniture'),
	('Dining Table', 2000, 20, 'Wooden dining table that seats 6 people.', 1, 'Furniture'),
	('Bookshelf', 1200, 25, '5-tier wooden bookshelf for home or office.', 1, 'Furniture'),
	('Yogurt Pack', 25, 150, 'Pack of 4 plain yogurt cups, 200g each.', 1, 'Dairy');

select * from Products;

select * from Products p
	inner join Administrators ad
	on p.Admin_ID = ad.Admin_ID
	inner join Categories c
	on p.Category_Name = c.Category_Name;


-- order --

insert into Orders (order_id ,  Order_Date , Status , Customer_ID)
values
	(1,'2025/12/04' , 'dd' , 2);

select * from Orders;

update orders
	-- Error
	set order_id = 'Preparing' -- Correct ==> set status = 'Preparing'
	where order_id = 1;

select * from Orders;

delete from orders
	where order_id = 0; -- with delete we should do condition but with truncate we don't

select * from Orders;
## back to again insert

update orders
	set status = 'Preparing'
	where order_id = 1;

select * from orders;

INSERT INTO Orders (Order_ID, Order_Date, Status, Customer_ID)
VALUES
	(2, '2025-11-05', 'Prepared', 2),
	(3, '2025-11-05', 'IN Delivery', 4),
	(4, '2025-11-06', 'Delivered', 9),
	(5, '2025-11-06', 'Preparing', 6),
	(6, '2025-11-07', 'Prepared', 7),
	(7, '2025-11-07', 'IN Delivery', 8);

select * from orders;

select * from orders o
	inner join customers c
	on c.customer_id = o.customer_id
	inner join users u
	on u.user_id = c.customer_id;
    
INSERT INTO Order_Items (Order_ID, Product_ID, unit_price , quantity , total_price)
VALUES
(1, 1 ,10,3,30), (1, 2 ,15,2,30), (1, 3 , 50,2,100), (1, 10 , 25,1,25),
(2, 4 , 5000,1,5000), (2, 5 , 3000,1,3000), (2, 6,250,3,750),
(3, 7 ,800,2,1600), (3, 8,2000,2,4000), (3, 9,1200,1,1200),
(4, 1 ,10,5,50), (4, 5,3000,3,9000), (4, 9,1200,2,2400),
(5, 2 , 15,2,30), (5, 4 , 5000,2,10000), (5, 6 , 250,2,500), (5, 10,25,2,50),
(6, 3 ,50,4,200), (6, 7 , 800 ,1,800), (6, 8 , 2000,3,6000),
(7, 1 ,10,6,60), (7, 2 ,15,4,60), (7, 3 ,50 ,1 ,50), (7, 4 , 5000,1,5000);

select * from order_items;

SELECT
    oi.order_id,
    oi.product_id,
    
    -- Order Info
    o.order_date,
    o.status,
    o.customer_id,
    
    -- Product Info
    p.product_name,
    p.description AS product_description,
    p.current_price,
    p.stock,
    p.category_name,
    
    -- Category Info
    ca.admin_id AS category_admin_id,
    ca.description AS category_description,

    -- Customer User Info
    uc.user_name AS customer_username,
    uc.ssn AS customer_ssn,
    uc.first_name AS customer_first_name,
    uc.last_name AS customer_last_name,
    uc.sex AS customer_sex,
    uc.register_date AS customer_register_date,
    uc.email AS customer_email,

    -- Admin User Info (Product Admin)
    ua.user_name AS admin_username,
    ua.ssn AS admin_ssn,
    ua.first_name AS admin_first_name,
    ua.last_name AS admin_last_name,
    ua.sex AS admin_sex,
    ua.register_date AS admin_register_date,
    ua.email AS admin_email

FROM order_items oi
INNER JOIN orders o
    ON oi.order_id = o.order_id
INNER JOIN products p
    ON p.product_id = oi.product_id
INNER JOIN categories ca
    ON p.category_name = ca.category_name
INNER JOIN customers c
    ON o.customer_id = c.customer_id
INNER JOIN users uc      -- Customer
    ON uc.user_id = c.customer_id
INNER JOIN administrators a
    ON p.admin_id = a.admin_id
INNER JOIN users ua      -- Admin (who added the product)
    ON ua.user_id = a.admin_id;


-- orderstatushistory --

insert orderstatushistory (old_status , new_status , update_date , customer_id , agent_id , order_id)
values
	('Preparing' , 'Prepared' , '2025/12/01' , 2 ,3 , 1),
	('Preparing' , 'IN Delivery' , '2025/12/01' , 2 ,3 , 1),
	('Preparing' , 'Waiting' , '2025/12/01' , 2 ,3 , 1);

insert orderstatushistory (old_status , new_status , update_date , customer_id , agent_id , order_id)
values
	('Preparing' , 'Prepared' , '2025/12/02' , 6 ,5 , 5),
    ('Delivered' , 'IN Delivery' , '2025/12/02' , 6 ,5 , 5),
    ('Preparing' , 'Declined' , '2025/12/02' , 6 ,5 , 5);
	
insert orderstatushistory (old_status , new_status , update_date , customer_id , agent_id , order_id)
values
	('Preparing' , 'Waiting' , '2025/12/02' , 6 ,5 , 5);
    
select * from orderstatushistory;
    
    
-- payments --

insert into payments( payment_date , amount_paied , order_id)
values
	('2025/12/02' , 1000 , 5),
	('2025/12/01' , 2000 , 1);

select* from payments;


-- cash --

insert into cash (payment_id , remained)
values
	(1 , 200);

select* from cash;


-- card

insert into card (payment_id , bank_account)
values
	(2 , 'AL_AHLY');
    
select * from card;


-- 'Preparing', 'Prepared', 'IN Delivery', 'Delivered','Waiting','Declined'

