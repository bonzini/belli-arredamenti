<?php

class Column
{
  var $table;

  function __construct (&$parent)
  {
    $this->table = $parent->add_column ($this);
  }

  function render_header ()
  {
    echo '&nbsp;';
  }

  function render ($n, $num, &$row, $full_subtable)
  {
    echo '&nbsp;';
  }

  function execute ()
  {
    return FALSE;
  }
}

class Action extends Column
{
  var $title;
  var $id_field;

  function __construct (&$parent, $title, $id_field = FALSE)
  {
    parent::__construct ($parent);
    $this->title = $title;
    $this->id_field = $id_field === FALSE ? $this->table->id_field : $id_field;
  }

  function render_action ($url)
  {
    echo '<a href="', htmlspecialchars ($url), '">', $this->title, '</a><br />';
  }
}

class URLAction extends Action
{
  var $url_prefix;

  function __construct (&$parent, $url_prefix, $title, $id_field = FALSE)
  {
    parent::__construct ($parent, $title, $id_field);
    $this->url_prefix = $url_prefix;
  }

  function render ($n, $num, &$row, $full_subtable)
  {
    if ($row === FALSE)
      $this->render_action ($this->url_prefix);
    else
      $this->render_action ($this->url_prefix . $row[$this->id_field]);
  }
}

class DeleteAction extends Action
{
  var $subtables;

  function __construct (&$parent, $title, $subtables = FALSE, $id_field = FALSE)
  {
    parent::__construct ($parent, $title, $id_field);
    $this->subtables = $subtables === FALSE ? array () : $subtables;
  }

  function render ($n, $num, &$row, $full_subtable)
  {
    if ($full_subtable)
      echo '&mdash;';
    else
      $this->render_action (
	  '?del_' . $this->table->db_table . '_id'
	  . '=' . $row[$this->id_field]);
  }

  function execute ()
  {
    $f = 'del_' . $this->table->db_table . '_id';
    if (isset ($_GET[$f]))
      $id = $_GET[$f];
    else if (isset ($_POST[$f]))
      $id = $_POST[$f];
    else
      return FALSE;

    global $sqlconn;
    $q = 'delete from ' . $this->table->db_table
	  . ' where ' . $this->id_field . '="' . mysqli_real_escape_string($sqlconn, $id) . '"';
    mysqli_query($sqlconn, $q);

    foreach ($this->subtables as $table => $field)
      {
	$q = 'delete from ' . $table
	      . ' where ' . $field . '="' . mysqli_real_escape_string($sqlconn, $id) . '"';
	mysqli_query($sqlconn, $q);
      }

    return TRUE;
  }
}

class TemplateColumn extends Column
{
  var $header;
  var $field;

  function __construct (&$parent, $header, $template)
  {
    parent::__construct ($parent);
    $this->header = $header;
    $this->template = $template;
  }

  function render_header ()
  {
    echo $this->header;
  }

  function render ($n, $num, &$row, $full_subtable)
  {
    echo preg_replace ('/@([0-9A-Za-z_]+)/e', "\$row['\\1']",
		       $this->template);
  }
}

class FieldColumn extends Column
{
  var $header;
  var $field;

  function __construct (&$parent, $header, $field)
  {
    parent::__construct ($parent);
    $this->header = $header;
    $this->field = $field;
  }

  function render_header ()
  {
    echo $this->header;
  }

  function render ($n, $num, &$row, $full_subtable)
  {
    echo $row[$this->field];
  }
}

class Footer
{
  var $table;
  var $column;
  var $from, $to;
  
  function __construct (&$table, $from = 1, $to = -1)
  {
    $this->table = &$table;
    $table->footers[$from] = &$this;
    $this->from = $from;
    $this->to = $to;
  }

  function add_column (&$column)
  {
    $this->column = &$column;
    return $this->table->table;
  }

  function execute ()
  {
    return $this->column->execute ();
  }

  function render ($n, $num, &$row)
  {
    $this->column->render ($n, $num, $row, FALSE);
  }
}

class Table
{
  var $table;
  var $select;
  var $where;
  var $group_by;
  var $order;
  var $id_field;
  var $db_table;
  var $columns, $subtables, $footers;
  var $header;
  var $sorted;

  function __construct ($db_table, $sql = FALSE, $id_field = 'id', $header = TRUE)
  {
    $this->header = $header;
    $this->db_table = $db_table;
    $this->order = $sql !== FALSE && isset ($sql['order']) ? $sql['order'] : FALSE;
    $this->where = $sql !== FALSE && isset ($sql['where']) ? $sql['where'] : FALSE;
    $this->group_by = FALSE;
    $this->id_field = $id_field;
    $this->columns = array ();
    $this->subtables = array ();
    $this->footers = array ();
    $this->sorted = false;
    $this->select = 'select * from ' . $this->db_table;
  }

  function set_query ($q)
  {
    if (preg_match ('/\bwhere\b/i', $q, $matches, PREG_OFFSET_CAPTURE))
      {
        $pos = $matches[0][1];
	$this->where = substr ($q, $pos + 6);
	$q = substr ($q, 0, $pos);
      }
    else
      $this->where = FALSE;

    if (preg_match ('/\bgroup\s+by\b/i', $q, $matches, PREG_OFFSET_CAPTURE))
      {
        $pos = $matches[0][1];
	$this->group_by = substr ($q, $pos + 8);
	$q = substr ($q, 0, $pos);
      }
    else
      $this->group_by = FALSE;

    if (preg_match ('/\border\s+by\b/i', $q, $matches, PREG_OFFSET_CAPTURE))
      {
        $pos = $matches[0][1];
	$this->order = substr ($q, $pos + 8);
	$q = substr ($q, 0, $pos);
      }
    else
      $this->order = FALSE;

    $this->select = $q;
  }

  function add_column (&$column)
  {
    $this->columns[] = &$column;
    return $this;
  }

  function execute ($redirect = FALSE)
  {
    $found_cmd = FALSE;
    foreach ($this->columns as $c)
      $found_cmd = $c->execute () || $found_cmd;
    foreach ($this->subtables as $t)
      $found_cmd = $t->execute () || $found_cmd;
    foreach ($this->footers as $f)
      $found_cmd = $f->execute () || $found_cmd;

    if ($found_cmd)
      {
	if ($redirect === FALSE)
	  $redirect = $_SERVER['PHP_SELF'];
        header ('Location: ' . $redirect);
	exit ();
      }
  }

  function render ($n = FALSE, $num = FALSE, $row = FALSE)
  {
    echo '<table>';
    $this->render_header ();
    $num = $this->render_body ($row);
    $this->render_footer ($n, $num, $row);
    echo '</table>';
    return $num;
  }

  function query ($row)
  {
    $q = $this->select;
    if ($this->where !== FALSE)
      $q .= ' where ' . $this->where;
    if ($this->group_by !== FALSE)
      $q .= ' group by ' . $this->group_by;
    if ($this->order !== FALSE)
      $q .= ' order by ' . $this->order;

    global $sqlconn;
    return mysqli_query($sqlconn, $q);
  }

  function render_header ()
  {
    if ($this->header)
      {
	echo '<thead><tr>';
	foreach ($this->columns as $c)
	  {
	    echo '<th>';
	    $c->render_header ();
	    echo '</th>';
	  }
	echo '</tr></thead>';
      }
  }

  function render_body ($parent_row)
  {
    $result = $this->query ($parent_row);
    $num = mysqli_num_rows($result);

    echo '<tbody>';
    for ($n = 1; $row = mysqli_fetch_array($result); $n++)
      $this->render_row ($n, $num, $row);
    echo '</tbody>';
    return $num;
  }

  function render_footer ($n, $num, &$row)
  {
    if (count ($this->footers) == 0)
      return;

    if (!$this->sorted)
      {
	ksort ($this->footers);
	$this->sorted = true;
      }

    echo '<tfoot><tr>';
    $last = 1;
    foreach ($this->footers as $f)
      {
	if ($f->from > $last)
	  echo '<td colspan="', $f->from - $last, '">&nbsp;</td>';
	$n = $f->to == -1 ? 1 : $f->to - $f->from + 1;
	echo '<td';
	if ($n > 1)
	  echo ' colspan="', $n, '"';
	echo '>';
        $f->render ($n, $num, $row);
	echo '</td>';
	$last = $f->to + 1;
      }

    echo '</tr></tfoot>';
  }

  function render_row ($n, $num, &$row)
  {
    ob_start ();
    $one_full_subtable = FALSE;
    foreach ($this->subtables as $t)
      {
        ob_start ();
	echo '<tr>';
	if ($t->from > 1)
	  echo '<td colspan="', $t->from - 1, '">&nbsp;</td>';
	$n = ($t->to == -1 ? count ($this->columns) : $t->to) - $t->from + 1;
	echo '<td';
	if ($n > 1)
	  echo ' colspan="', $n, '"';
	echo '>';
        $full_subtable = $t->render ($n, $num, $row);
	$one_full_subtable = $one_full_subtable || $full_subtable;
	echo '</td></tr>';
	if ($full_subtable || count ($t->footers) > 0)
          ob_end_flush ();
	else
	  ob_end_clean ();
      }
    
    $subtable_contents = ob_get_contents ();
    ob_end_clean ();

    echo '<tr>';
    foreach ($this->columns as $c)
      {
        echo '<td>';
        $c->render ($n, $num, $row, $one_full_subtable);
        echo '</td>';
      }
    echo '</tr>';
    echo $subtable_contents;
  }
}

class SubTable extends Table
{
  var $link_field;
  var $from, $to;
  
  function __construct (&$table, $db_table, $link_field, $sql = FALSE, $id_field = 'id',
		     $from = 1, $to = -1, $header = FALSE)
  {
    parent::__construct ($db_table, $sql, $id_field, $header);
    $this->table = &$table;
    $table->subtables[] = &$this;
    $this->link_field = $link_field;
    $this->from = $from;
    $this->to = $to;
  }

  function query ($row)
  {
    global $sqlconn;

    $q = $this->select . ' where ';
    $q .= $this->link_field . '= "'
	. mysqli_real_escape_string($sqlconn, $row[$this->table->id_field]) . '"';
    if ($this->where !== FALSE)
      $q .= ' and (' . $this->where . ')';
    if ($this->group_by !== FALSE)
      $q .= ' group by ' . $this->group_by;
    if ($this->order !== FALSE)
      $q .= ' order by ' . $this->order;

    return mysqli_query($sqlconn, $q);
  }
}

?>
