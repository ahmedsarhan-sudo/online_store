SELECT Users.user_id, Users.first_name, Users.last_name, Users.email, Customers.points
FROM Customers
JOIN Users ON Users.user_id = Customers.customer_id;

SELECT Products.product_name, Products.current_price, Categories.category_name
FROM Products
JOIN Categories ON Categories.category_name = Products.category_name;

SELECT Products.product_name, Users.first_name, Users.last_name
FROM Products
JOIN Administrators ON Administrators.admin_id = Products.admin_id
JOIN Users ON Users.user_id = Administrators.admin_id;

SELECT Orders.order_id, Orders.status, Users.first_name, Users.last_name
FROM Orders
JOIN Customers ON Customers.customer_id = Orders.customer_id
JOIN Users ON Users.user_id = Customers.customer_id;

SELECT Order_Items.order_id, Products.product_name, Products.current_price
FROM Order_Items
JOIN Products ON Products.product_id = Order_Items.product_id;

SELECT OrderStatusHistory.history_id, OrderStatusHistory.old_status, OrderStatusHistory.new_status,
       Users.first_name, Users.last_name
FROM OrderStatusHistory
JOIN Customers ON Customers.customer_id = OrderStatusHistory.customer_id
JOIN Users ON Users.user_id = Customers.customer_id;

SELECT Orders.order_id, Orders.order_date, Users.first_name, Products.product_name
FROM Orders
JOIN Customers ON Customers.customer_id = Orders.customer_id
JOIN Users ON Users.user_id = Customers.customer_id
JOIN Order_Items ON Order_Items.order_id = Orders.order_id
JOIN Products ON Products.product_id = Order_Items.product_id;