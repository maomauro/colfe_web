<?php
if (isset($_POST['reentrenar'])) {
    $output = shell_exec('conda run -n colfe_ml python C:\app\colfe_ml\reentrenar_modelo.py 2>&1');
    echo htmlspecialchars($output);
}
?>