<?php

# Elimina le slash
if (get_magic_quotes_gpc ())
  {
    function stripslashes_deep($value)
    {
      $value = is_array($value) ?
               array_map('stripslashes_deep', $value) :
               stripslashes($value);

      return $value;
    }

    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);

    # Attenti a eventuali problemi di sicurezza, con simpatiche query come
    #   http://foo.com/script.php?_SERVER[REMOTE_ADDR]=127.0.0.1
    # Ma i cookie di Google che iniziano per __ vanno bene.
    foreach ($_COOKIE as $x => $y)
      if ((substr ($x, 0, 1) == '_' && substr ($x, 0, 2) != '__')
          || isset ($_SERVER[$x])) die;

    foreach ($_GET as $x => $y)
      if ((substr ($x, 0, 1) == '_' && substr ($x, 0, 2) != '__')
          || isset ($_SERVER[$x])) die;

    foreach ($_POST as $x => $y)
      if ((substr ($x, 0, 1) == '_' && substr ($x, 0, 2) != '__')
          || isset ($_SERVER[$x])) die;

    extract ($_COOKIE);
    extract ($_GET);
    extract ($_POST);
  }

# mysql_execute ($connessione, $query, $arg1, $arg2, ...)
# I ? in $query sono sostituiti da mysql_escape_string ($argN)
function mysql_execute ()
{
  $args = func_get_args ();
  $conn = $args[0];

  $query = call_user_func_array ('mysql_make_query', $args);
  #echo "<!-- $query -->";
  #if (preg_match ('/SELECT/i', $query))
  #  return mysql_query ($query);
  #else
  #  return print ($query);

  $result = mysqli_query($conn, $query);
  if (!$result)
    trigger_error(mysqli_error($conn) . '<br/>' . htmlspecialchars ($query), E_USER_ERROR);

  return $result;
}

# mysql_unbuffered_execute ($connessione, $query, $arg1, $arg2, ...)
# I ? in $query sono sostituiti da mysql_escape_string ($argN)
function mysql_unbuffered_execute ()
{
  $args = func_get_args ();
  $conn = $args[0];

  $query = call_user_func_array ('mysql_make_query', $args);
  #echo "<!-- $query -->";
  $result = mysqli_query($conn, $query, MYSQLI_USE_RESULT);
  if (!$result)
    trigger_error(mysqli_error($conn) . '<br/>' . htmlspecialchars ($query), E_USER_ERROR);

  return $result;
}

function mysql_check_one_row ($result)
{
  # Occorre leggere tutti i record nel caso ce ne sia piu' di uno,
  # nel caso in cui la query sia eseguita in modalita' unbuffered
  $n = 1;
  while (mysqli_fetch_row($result))
    $n++;
  if ($n > 1)
    trigger_error('attesi 0 o 1 record', E_USER_ERROR);
}

function mysql_execute_fetch_row ()
{
  $args = func_get_args ();
  $result = call_user_func_array ('mysql_unbuffered_execute', $args);
  $row = mysqli_fetch_row($result);
  if (!$row)
    return FALSE;
  mysql_check_one_row ($result);
  return $row;
}

function mysql_execute_fetch_val ()
{
  $args = func_get_args ();
  $result = call_user_func_array ('mysql_unbuffered_execute', $args);
  $row = mysqli_fetch_row($result);
  if ($row)
    mysql_check_one_row ($result);
  return $row[0];
}

function mysql_make_query ()
{
  $args = func_get_args ();
  $conn = $args[0];

  array_shift($args);
  $args[0] = str_replace ('%', '%%', $args[0]);
  $args[0] = str_replace ('?', '%s', $args[0]);

  foreach ($args as $i => $v)
    {
      if ($i == 0 || is_int ($v))
	continue;

      $args[$i] = '\''.mysqli_real_escape_string($conn, $v).'\'';
    }

  return call_user_func_array ('sprintf', $args);
}

?>
