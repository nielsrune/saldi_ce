<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldi</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="rental.css">
    <!-- Include Flatpickr CSS -->
    <link rel="stylesheet" href="../css/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="../css/flatpickrDark.css">

    <!-- Include Flatpickr JavaScript -->
    <script src="../javascript/flatpickr.min.js"></script>
    <script src="../javascript/flatpickrDa.js"></script>
</head>
<body>
    <?php 
    $side = "booking";
    include "header.php" 
    ?>
    <div id="loading">
    <img id="loading-image" src="https://upload.wikimedia.org/wikipedia/commons/c/c7/Loading_2.gif?20170503175831" alt="Loading..." />
    </div>
        <div class="width-80">
            <form class="form">
                <div class="form-group">
                    <label for="from">Vælg start dato</label>
                        <input type="text" class="from">
                </div>
                <div class="form-group">
                    <label for="weeks">Vælg uge tal:</label>
                    <input name="weeks" type="number" class="form-control weeks">
                </div>
                <div class="form-group">
                    <label for="customers">Vælg brugere</label>
                    <select id="inputCustomers" name="customer" class="form-control customers" required>
                    </select>
                </div>
                <div class="changing-input row">
                </div>
                <div class="form-group">
                    <label for="items">Vælg vare til udlejning</label>
                    <select id="inputItems" name="item" class="form-control items" required>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Lav Booking</button>
            </form>
        </div>
</div>
</div>
    <script src="bootstrap.min.js"></script>
    <script src="weeks.js?<?php echo time(); ?>" type="module"></script>
</body>
</html>