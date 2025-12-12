<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RFID Attendance Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            padding: 20px;
            color: #2c3e50;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            width: 250px;
            height: 150px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .card h2 {
            margin: 10px 0;
            color: #2980b9;
        }

        .card p {
            margin: 0;
            font-size: 14px;
        }

        @media screen and (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <h1>RFID Attendance System</h1>
    <div class="container">
        <a href="add_student.php" class="card">
            <h2>Add Student</h2>
            <p>Register a new student and their RFID card</p>
        </a>

       <a href="BLOCK_1.php" class="card">
            <h2>Block 1 Attendance</h2>
            <p>View or monitor attendance for Block 1</p>
       </a>

        <a href="BLOCK_2.php" class="card">
            <h2>Block 2 Attendance</h2>
            <p>View or monitor attendance for Block 2</p>
        </a>

         <a href="BLOCK_3.php" class="card">
            <h2>Block 3 Attendance</h2>
            <p>View or monitor attendance for Block 3</p>
        </a>

         <a href="BLOCK_4.php" class="card">
            <h2>Block 4 Attendance</h2>
            <p>View or monitor attendance for Block 4</p>
        </a>

         <a href="BLOCK_5.php" class="card">
            <h2>Block 5 Attendance</h2>
            <p>View or monitor attendance for Block 5</p>
        </a>
    </div>
</body>
</html>
