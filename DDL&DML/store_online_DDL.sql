create database online_store;

use online_store;

--------- users ---------

create table Users(
	User_ID bigint primary key auto_increment,
	User_Name varchar(256) unique not null,
	SSN int unique not null,
	First_Name varchar(100) not null,
	Last_Name varchar(100) not null,
	Sex enum('Male','Female','Other'),
	Register_Date date,
	password varchar(256) not null,
	Email varchar(256) not null
);

ALTER TABLE users 
	ADD COLUMN password_plain VARCHAR(256) NOT NULL;

alter table Users
	modify column SSN varchar(14) not null unique;

SHOW INDEX FROM Users;

alter table Users
	drop index SSN_2;

alter table Users
	modify SSN varchar(14) not null;


------------- admin -------------

create table Administrators(
	Admin_ID bigint primary key,
	constraint my_fk1 foreign key(Admin_ID) references Users(User_ID)
);


-------------- customer -----------

create table Customers(
	Customer_ID bigint primary key,
	points float,
	constraint my_fk2 foreign key(Customer_ID) references Users(User_ID)
);


------------- agent --------------

create table Agents(
	Agent_ID bigint primary key,
	Office int not null,
	constraint my_fk3 foreign key (Agent_ID) references Users(User_ID)
);


----------- user phone -------

create table User_Phone(
	User_ID bigint,
    Phone varchar(22),
    constraint my_pk1 primary key(User_ID , Phone)
);

alter table User_Phone
	drop primary key;

alter table User_Phone
	add primary key (User_ID,Phone);


---------- category ------------

create table Category(
	Admin_ID bigint,
	Category_Name varchar(100) primary key,
	Description varchar(400) not null,
	constraint my_fk4 foreign key (Admin_ID) references Administrators(Admin_ID)
);

alter table Category
	rename Categories;


---------- product ----------

create table Product(
	Product_ID bigint primary key auto_increment,
	Product_Name varchar(100) unique not null,
	Description varchar(400) not null,
	Current_Price float not null,
	stock bigint not null ,
	Admin_ID bigint,
	Category_Name varchar(100),
	constraint my_fk5 foreign key(Admin_ID) references Administrators(Admin_ID),
	constraint my_fk6 foreign key(Category_Name) references Categories(Category_Name)
);

alter table Product
	rename Products;


---------- orders ------------

create table Orders(
	Order_ID bigint primary key,
	Order_Date date not null,
	Status enum('Preparing','Prepared','IN Delivery','Delivered','Waiting','Declined') not null,
	Customer_ID bigint,
	constraint my_fk7 foreign key(Customer_ID) references Customers(Customer_ID)
);

alter table orders
	modify Status enum('Preparing','Prepared','IN Delivery','Delivered','Waiting','Declined') not null;

alter table orders
	modify Order_ID bigint auto_increment;


---------- order_items --------

create table Order_Items(
	Order_ID bigint,
	Product_ID bigint,
	primary key (Order_ID , Product_ID),
	constraint my_fk8 foreign key(Order_ID) references Orders(Order_ID),
	constraint my_fk9 foreign key(Product_ID) references Products(Product_ID)
);
alter table Order_Items
	add unit_price float not null;

alter table Order_Items
	add quantity int default 1;
    
alter table Order_Items
	add total_price float not null;


-------- orderstatus -------

create table OrderStatusHistory(
	History_ID bigint primary key auto_increment,
	Old_Status enum('Preparing','Prepared','IN Delivery','Delivered','Waiting','Declined') not null,
	New_Status enum('Preparing','Prepared','IN Delivery','Delivered','Waiting','Declined') not null,
	Update_Date date not null,
	Customer_ID bigint,
	Agent_ID bigint,
	Order_ID bigint,
	constraint my_fk10 foreign key(Customer_ID) references Customers(Customer_ID),
	constraint my_fk11 foreign key(Agent_ID) references Agents(Agent_ID),
	constraint my_fk12 foreign key(Order_ID) references Orders(Order_ID)
);

alter table OrderStatusHistory
	modify Old_Status enum('Preparing','Prepared','IN Delivery','Delivered','Waiting','Declined') not null;
    
alter table OrderStatusHistory
	modify New_Status enum('Preparing','Prepared','IN Delivery','Delivered','Waiting','Declined') not null;


------- payment ---------

create table Payments(
	Payment_ID bigint primary key,
	payment_Date date not null,
	Amount_Paied float not null,
	Order_ID bigint,
	constraint my_fk13 foreign key(Order_ID) references Orders(Order_ID) 
);

alter table payments
	modify Payment_ID bigint auto_increment;


------- cash --------

create table Cash(
	Payment_ID bigint primary key,
	Remained float not null,
	constraint my_fk14 FOREIGN KEY (Payment_ID) REFERENCES Payments (Payment_ID)
);


------- card -------

CREATE TABLE Card(
    Payment_ID bigINT PRIMARY KEY,
    Bank_Account VARCHAR(256) NOT NULL,
    FOREIGN KEY (Payment_ID) REFERENCES Payments (Payment_ID)
);



