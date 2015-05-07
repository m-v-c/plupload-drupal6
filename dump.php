<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<title>Form dump</title>
</head>
<body style="font: 13px Verdana; background: #eee; color: #333">
	
<h1>Form submit dump</h1>

<table>
  <caption><code>$_SERVER</code></caption>
  <thead>
    <tr>
      <th>Name</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    <?php $count = 0; foreach ($_SERVER as $name => $value) { ?>
    <tr class="<?php echo $count % 2 == 0 ? 'alt' : ''; ?>">
      <td><?php echo htmlentities(stripslashes($name)) ?></td>
      <td><?php echo nl2br(htmlentities(stripslashes($value))) ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>

<table>
  <caption><code>$_POST</code></caption>
  <thead>
    <tr>
      <th>Name</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    <?php $count = 0; foreach ($_POST as $name => $value) { ?>
    <tr class="<?php echo $count % 2 == 0 ? 'alt' : ''; ?>">
      <td><?php echo htmlentities(stripslashes($name)) ?></td>
      <td><?php echo nl2br(htmlentities(stripslashes($value))) ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>

<table>
  <caption><code>$_GET</code></caption>
  <thead>
    <tr>
      <th>Name</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    <?php $count = 0; foreach ($_GET as $name => $value) { ?>
    <tr class="<?php echo $count % 2 == 0 ? 'alt' : ''; ?>">
      <td><?php echo htmlentities(stripslashes($name)) ?></td>
      <td><?php echo nl2br(htmlentities(stripslashes($value))) ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>

</body>
</html>
