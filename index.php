<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer</title>
</head>

<body>
    <?php

    global $DB;
    $database = "rendeles";

    $DB = mysqli_connect("localhost", "root", null, $database);


    print ("<h1>DATABASE VIEWER</h1>");
    print ("<h2><q>$database</q></h2>");

    print ("<ul>");
    print ("<li>Táblák<ul>");
    
    $tables = [];
    $Q = mysqli_query($DB, "SHOW TABLES");
    
    while ($sor = mysqli_fetch_array($Q)) {

        foreach ([$sor[0]] as $table) {
            print("<li><a href='?t={$table}'>{$table}</a></li>");
        }
    }
    
    print ("</ul></li>");

    $current_table = isset($_GET['t']) ? $_GET['t'] : '';

    print ("<li>MŰVELETEK" . (in_array($current_table, $tables) ? " (<q>{$current_table}</q>)" : "") . "<ul>");

    if ($current_table) {
        print ("<li><a href='?t={$current_table}&o=szerkezet'>Szerkezet</a></li>");
        print ("<li><a href='?t={$current_table}&o=tartalom'>Tartalom</a></li>");
        print ("<li><a href='?t={$current_table}&o=indexek'>Indexek</a></li>");
        print ("<li><a href='?t={$current_table}&o=kereses'>Keresés</a></li>");
    } else {
        print ("<li>Szerkezet</li><li>Tartalom</li><li>Indexek</li>");
    }
    print ("</ul></li>");
    print ("</ul>");

    if ($current_table) {
        $operation = isset($_GET['o']) ? $_GET['o'] : '';

        switch ($operation) {
            case 'szerkezet':
                $Q = mysqli_query($DB, "DESCRIBE `$current_table`");
                print("<h2>Szerkezet: $current_table</h2><table border='1'><tr><th>Field</th><th>Type</th></tr>");
                while ($row = mysqli_fetch_array($Q)) {
                    print("<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>");
                }
                print("</table>");
                break;

                case 'tartalom':
                    $Q = mysqli_query($DB, "SELECT * FROM `$current_table`");
                    print("<h2>Tartalom: $current_table</h2><table border='1'><tr>");
                    
                    $columns = mysqli_fetch_fields($Q);
                    foreach ($columns as $column) {
                        print("<th>{$column->name}</th>");
                    }
                    print("</tr>");
                    
                    while ($row = mysqli_fetch_assoc($Q)) {
                        print("<tr>");
                        foreach ($columns as $column) {
                            $cell = $row[$column->name];
                            $type = $column->type;
                    
                            switch ($type) {
                                case MYSQLI_TYPE_VAR_STRING:
                                    $color = 'brown';
                                    $alignment = 'left';
                                    break;
                                case MYSQLI_TYPE_TINY:
                                case MYSQLI_TYPE_SHORT:
                                case MYSQLI_TYPE_INT24:
                                case MYSQLI_TYPE_LONG:
                                case MYSQLI_TYPE_LONGLONG:
                                case MYSQLI_TYPE_FLOAT:
                                case MYSQLI_TYPE_DOUBLE:
                                case MYSQLI_TYPE_DECIMAL:
                                case MYSQLI_TYPE_NEWDECIMAL:
                                case MYSQLI_TYPE_BIT:
                                    $color = 'blue';
                                    $alignment = 'right';
                                    break;
                                case MYSQLI_TYPE_DATE:
                                case MYSQLI_TYPE_DATETIME:
                                case MYSQLI_TYPE_TIMESTAMP:
                                    $color = 'green';
                                    $alignment = 'left';
                                    break;
                                case MYSQLI_TYPE_NULL:
                                    $color = 'gray';
                                    $alignment = 'left';
                                    break;
                                default:
                                    $color = 'black';
                                    $alignment = 'left';
                                    break;
                            }
                            print("<td style='color: {$color}; text-align: {$alignment};'>{$cell}</td>");
                        }
                        print("</tr>");
                    }
                    
                
                    print("</table>");
                    break;
                

            case 'indexek':
                $Q = mysqli_query($DB, "SHOW INDEXES FROM `$current_table`");
                print("<h2>Indexek: $current_table</h2><table border='1'><tr><th>Key Name</th><th>Column Name</th></tr>");
                while ($row = mysqli_fetch_array($Q)) {
                    print("<tr><td>{$row['Key_name']}</td><td>{$row['Column_name']}</td></tr>");
                }
                print("</table>");
                break;
            
            case 'kereses':
                $attributes = [];
                $Q = mysqli_query($DB, "DESCRIBE `$current_table`");
                while ($row = mysqli_fetch_array($Q)) {
                    $attributes[] = $row['Field'];
                }

                print("<h2>Keresés: $current_table</h2>");
                print("<form method='GET' action=''>");
                print("<input type='hidden' name='t' value='{$current_table}'>");
                print("<select name='attribute'>");
                foreach ($attributes as $attribute) {
                    print("<option value='{$attribute}'>{$attribute}</option>");
                }
                print("</select>");
                print("<input type='text' name='search' placeholder='Search...'>");
                print("<input type='submit' value='Keresés'>");
                print("</form>");

                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $searchValue = mysqli_real_escape_string($DB, $_GET['search']);
                    $attribute = mysqli_real_escape_string($DB, $_GET['attribute']);
                    $query = "SELECT * FROM `$current_table` WHERE `$attribute` LIKE '%$searchValue%'";
                    $result = mysqli_query($DB, $query);

                    print("<h3>Keresési eredmények:</h3><table border='1'><tr>");
                    $columns = mysqli_fetch_fields($result);
                    foreach ($columns as $column) {
                        print("<th>{$column->name}</th>");
                    }
                    print("</tr>");

                    while ($row = mysqli_fetch_assoc($result)) {
                        print("<tr>");
                        foreach ($columns as $column) {
                            $cell = $row[$column->name];
                            print("<td>{$cell}</td>");
                        }
                        print("</tr>");
                    }
                    print("</table>");
                }
                break;

            default:
            print("<p>Nincs megadva művelet a következő táblának: ".$current_table."</p>");
                break;
        }
    }

    ?>
</body>
</html>