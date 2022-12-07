<?php

?>

<html>
<head>
    <script>
        window.onload = function() {
            var links = new Array('./print_register.php?recpt_no=<?php echo $recpt_no; ?>', 'http://www.stackoverflow.com/');
            for(var i = 0; i < links.length; i++) {
                window.open(links[i]);
            }
        }
    </script>
</head>
<body>
...
</body>
</html>
