<?php
require_once 'config.php';

try {
    // Check especialidad_campos structure
     = 'DESCRIBE especialidad_campos';
     = ->prepare();
    ->execute();
     = ->fetchAll(PDO::FETCH_ASSOC);
    echo 'Table especialidad_campos structure:\n';
    print_r();

    // Check consulta_campos_valores structure
     = 'DESCRIBE consulta_campos_valores';
     = ->prepare();
    ->execute();
     = ->fetchAll(PDO::FETCH_ASSOC);
    echo '\n\nTable consulta_campos_valores structure:\n';
    print_r();

} catch (PDOException \) {
    echo 'Error: ' . \->getMessage();
}
?>
