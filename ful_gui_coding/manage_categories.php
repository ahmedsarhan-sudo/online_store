<?php
session_start();
include "db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// إضافة فئة جديدة
if (isset($_POST['add'])) {
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];
    $admin_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO Categories (Admin_ID, Category_Name, Description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $category_name, $description);

    if ($stmt->execute()) {
        $message = "Category added successfully!";
        $msg_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $msg_type = "error";
    }
}

// تعديل فئة موجودة
if (isset($_POST['edit'])) {
    $old_name = $_POST['old_name'];
    $new_name = $_POST['category_name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE Categories SET Category_Name=?, Description=? WHERE Category_Name=?");
    $stmt->bind_param("sss", $new_name, $description, $old_name);

    if ($stmt->execute()) {
        $message = "Category updated successfully!";
        $msg_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $msg_type = "error";
    }
}

// حذف فئة
if (isset($_POST['delete'])) {
    $category_name = $_POST['category_name'];

    // 1) التحقق إذا فيه منتجات مرتبطة بالفئة
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM Products WHERE Category_Name=?");
    $check->bind_param("s", $category_name);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        $message = "Cannot delete this category! There are $count product(s) associated with it.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM Categories WHERE Category_Name=?");
        $stmt->bind_param("s", $category_name);

        if ($stmt->execute()) {
            $message = "Category deleted successfully!";
            $msg_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $msg_type = "error";
        }
    }
}

// جلب كل الفئات
$result = $conn->query("SELECT * FROM Categories ORDER BY Category_Name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Admin</title>
    <style>
        /* =========================================
           1. CORE THEME & BACKGROUND
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            background-color: #0f172a;
        }

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.4);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.5), #0f172a 95%);
            z-index: -1;
        }

        /* =========================================
           2. GLASS PANELS & CONTAINERS
           ========================================= */
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 1000px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            margin-bottom: 30px;
        }

        h2 { font-size: 2.2em; margin-bottom: 25px; text-align: center; color: #fff; }
        h2 span { color: #f59e0b; } /* Amber */

        h3 {
            color: #10b981; /* Emerald */
            font-size: 1.4em;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
            padding-bottom: 10px;
        }

        /* =========================================
           3. FORMS & INPUTS
           ========================================= */
        label {
            display: block;
            font-size: 0.85em;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1px;
            margin-bottom: 8px;
            margin-top: 15px;
        }

        input[type="text"], textarea {
            width: 100%;
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            color: #fff;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        
        textarea { resize: vertical; min-height: 100px; }

        input:focus, textarea:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
            background: rgba(30, 41, 59, 0.9);
        }

        /* =========================================
           4. TABLE STYLING
           ========================================= */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            padding: 15px;
            color: #f59e0b;
            font-size: 0.9em;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(245, 158, 11, 0.3);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            vertical-align: top;
        }
        
        tr:hover td { background: rgba(255, 255, 255, 0.02); }

        .category-name { font-weight: 600; color: #fff; }
        .actions { white-space: nowrap; }

        /* =========================================
           5. BUTTONS
           ========================================= */
        button {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 0.9em;
        }

        .btn-add {
            background: #f59e0b;
            color: #0f172a;
            width: 100%;
            margin-top: 20px;
            padding: 15px;
            font-size: 1.1em;
        }
        .btn-add:hover { background: #d97706; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3); }

        .btn-edit { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); }
        .btn-edit:hover { background: #10b981; color: #0f172a; }

        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); margin-left: 5px; }
        .btn-delete:hover { background: #ef4444; color: #fff; }

        .btn-save { background: #10b981; color: #0f172a; margin-top: 10px; }
        .btn-cancel { background: transparent; color: #94a3b8; border: 1px solid #64748b; margin-top: 10px; }

        .btn-back {
            display: inline-block;
            padding: 14px 35px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s ease;
            text-align: center;
        }
        .btn-back:hover { background: #fff; color: #0f172a; transform: translateY(-2px); }

        /* =========================================
           6. ALERTS & UI WIDGETS
           ========================================= */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

        .edit-form {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .fade-in { opacity: 0; animation: fadeIn 0.8s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel fade-in">
    <h2>Manage <span>Categories</span></h2>

    <?php if(isset($message)): ?>
        <div class="alert <?= ($msg_type == 'success') ? 'alert-success' : 'alert-error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <h3>Add New Category</h3>
    <form method="POST">
        <label for="category_name">Category Name</label>
        <input type="text" name="category_name" placeholder="e.g. Electronics" required>

        <label for="description">Description</label>
        <textarea name="description" placeholder="Brief details about this category..." required></textarea>

        <button type="submit" name="add" class="btn-add">Add Category</button>
    </form>
</div>

<div class="glass-panel fade-in" style="animation-delay: 0.2s;">
    <h3>All Categories</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="category-name"><?= htmlspecialchars($row['Category_Name']) ?></td>
                    <td class="description"><?= htmlspecialchars($row['Description']) ?></td>
                    <td class="actions" style="text-align: right;">
                        <button class="btn-edit" onclick="toggleEdit(this)">Edit</button>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="category_name" value="<?= htmlspecialchars($row['Category_Name']) ?>">
                            <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                        </form>
                        
                        <div class="edit-form" style="text-align: left;">
                            <form action="" method="POST">
                                <input type="hidden" name="old_name" value="<?= htmlspecialchars($row['Category_Name']) ?>">
                                
                                <label>Category Name</label>
                                <input type="text" name="category_name" value="<?= htmlspecialchars($row['Category_Name']) ?>" required>
                                
                                <label>Description</label>
                                <textarea name="description" required><?= htmlspecialchars($row['Description']) ?></textarea>
                                
                                <button type="submit" name="edit" class="btn-save">Save Changes</button>
                                <button type="button" class="btn-cancel" onclick="cancelEdit(this)">Cancel</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<a href="admin_home.php" class="btn-back">Return to Admin Dashboard</a>

<script>
    // Smooth fade-in
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 1s';
            document.body.style.opacity = '1';
        }, 100);
    });

    // Toggle Edit Form logic
    function toggleEdit(btn) {
        // Close any other open edit forms first
        document.querySelectorAll('.edit-form').forEach(form => {
            if (form !== btn.parentElement.querySelector('.edit-form')) {
                form.style.display = 'none';
            }
        });
        
        const actionsCell = btn.parentElement;
        const editForm = actionsCell.querySelector('.edit-form');
        
        if (editForm.style.display === 'block') {
            editForm.style.display = 'none';
        } else {
            editForm.style.display = 'block';
        }
    }

    // Cancel Edit logic
    function cancelEdit(btn) {
        const editForm = btn.closest('.edit-form');
        editForm.style.display = 'none';
    }
</script>

</body>
</html>