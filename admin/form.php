<?php

class FormField
{
  var $id, $field;
  var $error;

  function __construct (&$form, $field)
  {
    $this->field = $field;
    $form->fields[$field] = &$this;
    $this->id = $form->table . '-' . $this->field;
    $this->error = FALSE;
    $this->form =& $form;
  }

  function render ()
  {
  }

  function validate ()
  {
    return TRUE;
  }

  function db_statement ()
  {
    return '';
  }

  function execute_post_insert ()
  {
  }

  function label ($id, $label)
  {
    echo '<label ';
    if ($this->error)
      echo 'class="error" ';
    echo ' for="', $id, '">', $label, '</label> ';
  }
}

class HTMLField extends FormField
{
  var $label;

  function __construct (&$form, $label)
  {
    $this->form =& $form;
    $form->fields[] = &$this;
    $this->label = $label;
  }

  function render ()
  {
    echo $this->label;
  }
}

class TemplateField extends FormField
{
  var $template;

  function __construct (&$form, $template)
  {
    parent::__construct ($form, '');
    $this->template = $template;
  }

  function render ()
  {
    echo preg_replace ('/@([0-9A-Za-z_]+)/e', "\$this->form->get_value('\\1')",
                       $this->template);
  }
}

class DBField extends FormField
{
  function db_statement ()
  {
    global $sqlconn;
    return $this->field . '="'
	   . mysqli_real_escape_string($sqlconn, $this->form->get_value ($this->field)) . '"';
  }
}

class HiddenField extends DBField
{
  var $value;

  function __construct (&$form, $field, $value = FALSE)
  {
    parent::__construct ($form, $field);
    $this->value = $value === FALSE ? $this->form->get_value ($this->field) : $value;
  }

  function render ()
  {
    echo '<input type="hidden" name="', $this->field, '" value="',
      htmlspecialchars ($this->value), '" /><br />';
  }
}

class IDField extends HiddenField
{
  function db_statement ()
  {
    return '';
  }
}

class TextAreaField extends DBField
{
  var $label, $default;
  var $rows, $width;
  var $validate;

  function __construct (&$form, $field, $label, $default = '',
			  $rows = 10, $width = '80%')
  {
    parent::__construct ($form, $field);
    $this->label = $label;
    $this->default = $default;
    $this->error = FALSE;
    $this->rows = $rows;
    $this->width = $width;
    $this->validate = FALSE;
  }

  function render ()
  {
    $this->label ($this->id, $this->label);
    echo '<textarea
	id="', $this->id, '" name="', $this->field, '" ',
	'rows="', $this->rows, '" style="width: ', $this->width, '">',
        htmlspecialchars ($this->form->get_value ($this->field, $this->default)),
	'</textarea>';
  }

  function validate ()
  {
    return ($this->validate === FALSE
	    || preg_match ($this->validate, $this->form->get_value ($this->field)));
  }
}

class TextField extends DBField
{
  var $label, $default, $validate;

  function __construct (&$form, $field, $label, $default = '', $validate = FALSE)
  {
    parent::__construct ($form, $field);
    $this->label = $label;
    $this->default = $default;
    $this->validate = $validate;
    $this->error = FALSE;
  }

  function render ()
  {
    $this->label ($this->id, $this->label);
    echo '<input
	id="', $this->id, '" name="', $this->field, '" value="',
        htmlspecialchars ($this->form->get_value ($this->field, $this->default)),
	'" /><br />';
  }

  function validate ()
  {
    return ($this->validate === FALSE
	    || preg_match ($this->validate, $this->form->get_value ($this->field)));
  }
}

class FileField extends DBField
{
  var $base;
  var $label;
  
  function __construct (&$form, $base, $label)
  {
    parent::__construct ($form, basename ($base));
    $this->base = FILEBASE . $base;
    $this->label = $label;
  }
  
  function get_file_ext ($name)
  {
    $dot = strrpos($name,'.'); 
    $slash = strpos($name,'/', $dot); 
    if ($dot !== FALSE && $slash === FALSE)
      return substr ($name, $dot + 1);
    else
      return "";
  }

  function file_size($file)
  {
    $size=filesize($file);
    if ($size<1024)                               	# <1 Kb prec. 1 byte
      return $size.'&nbsp;bytes';
    if ($size<10240)                                # <10 Kb prec. 100 byte
      return round($size/1024, 1).'&nbsp;KB';
    if ($size<1048576)                              # <1 Mb prec. 1k byte
      return round($size/1024).'&nbsp;KB';
    if ($size<1073741824)                           # < 1 Gb prec. 100k byte
      return round($size/1048576, 1).'&nbsp;MB';
    return round($size/1073741824, 2).'&nbsp;GB';   # > 1 Gb prec. 10 megabyte
  }

  function render ()
  {
    global $_root;
    
    echo "<h3><strong>$this->label</strong></h3>";
    $this->render_files ();

    $this->label ($this->id, 'Aggiungi file');
    echo '<input id="', $this->id,
      '" name="', $this->field, '" type="file" /><br />';
  }

  function render_files ()
  {
    $id = $this->form->id;
    $dirname = $this->base . '/' . $id.'/';

    if ($id && is_dir ($dirname) && $dir = @opendir($dirname))
      {
	echo '<ul class="checkbox-list">';
	for ($i = 0; ($file = readdir($dir)) !== FALSE; )
	  if (substr ($file, 0, 1) != '.')
	    $this->render_file ($i++, "$dirname$file");
	  
	echo '</ul>';
	closedir($dir);
      }
  }

  function render_file ($key, $fname)
  {
    global $_root;

    $f = basename ($fname);
    $ext = $this->get_file_ext ($f);
    $nome = substr ($f, 0, strlen ($f) - strlen ($ext) - 1);

    echo '<li><input type="hidden" name="', $this->field, '-name[', $key, ']" ',
      'value="', htmlspecialchars ($f), '" />',
      '<input type="checkbox" name="', $this->field, '-keep[', $key, ']" id="',
      $this->id, '-keep-', $key,
      '" value="yes" checked="checked" />&nbsp;<img src="', $_root, '/images/',
      $ext, '.gif" alt="', $ext, ' attachment"> <a href="',
      htmlspecialchars ($fname), '">',
      htmlspecialchars (str_replace ('_', ' ', $nome)), 
      '</a> <label for="', $this->id, '-keep-', $key, '">(',
      $ext, ' file, ', $this->file_size ($fname), ')</label></li>';
  }

  function db_statement ()
  {
    return '';
  }

  function execute_post_insert ()
  {
    $id = $this->form->id;
    $dirname = $this->base . '/';

    if (isset ($_POST[$this->field . '-name']))
      {
        foreach ($_POST[$this->field . '-name'] as $id_attachment => $name)
          {
            if ($name == "" || strpos ($name, '/') !== FALSE)
	      continue;

            if (!isset ($_POST[$this->field . '-keep'][$id_attachment]))
	      {
	        $this->delete_file ($dirname, $id . '/' . $name);
	        $this->delete_file_info (basename ($name));
	      }
          }
      }

    $save = umask(0);
    if(!file_exists ($dirname . $id))
      mkdir ($dirname . $id, 0755);

    if(is_uploaded_file($_FILES[$this->field]['tmp_name']))
      {
	$name = $id . '/' . strtolower(basename ($_FILES[$this->field]['name']));
	$ext = $this->get_file_ext ($name);
	$base = substr ($name, 0, strlen ($name) - strlen ($ext) - 1) . '_';
	for ($i = 1; file_exists ($dirname . $name); $i++)
	  $name = $base . $i . '.' . $ext;
	
	$this->upload_file ($_FILES[$this->field]['tmp_name'], $dirname, $name);
	$this->save_file_info (basename ($name));
      }
    
    umask ($save);
  }

  function delete_file ($dirname, $name)
  {
    unlink ($dirname . $name);
  }

  function upload_file ($src, $dirname, $name)
  {
    move_uploaded_file ($src, $dirname . $name);
    chmod ($dirname . $name, 0666);
  }

  function delete_file_info ($name)
  {
  }

  function save_file_info ($name)
  {
  }
}

class LinkedFileField extends FileField
{
  var $id_field;
  var $name_field;
  var $link_field;
  var $file_table;
  var $link;

  function __construct (&$form, $base, $label, $file_table, $id_field, $name_field,
		        $link_field, $link = FALSE)
  {
    parent::__construct ($form, $base, $label);
    $this->file_table = $file_table;
    $this->link = $link;

    list ($k, $v) = each ($link_field);
    $this->id_field = $id_field;
    $this->name_field = $name_field;

    global $sqlconn;
    $this->link_field = $k . ' = "' . mysqli_real_escape_string($sqlconn, $v) . '"';
  }
  
  function delete_file_info ($name)
  {
    global $sqlconn;
    mysqli_query($sqlconn, 'delete from ' . $this->file_table . ' where ' . $this->link_field .
	  ' and ' . $this->name_field . ' = "' . mysqli_real_escape_string($sqlconn, $name) . '"');
  }

  function save_file_info ($name)
  {
    global $sqlconn;
    mysqli_query($sqlconn, 'replace into ' . $this->file_table . ' set ' . $this->link_field .
	   ', ' .  $this->name_field . ' = "' . mysqli_real_escape_string($sqlconn, $name) . '"');
  }

  function render_files ()
  {
    $q = 'select * from ' . $this->file_table . ' where ' . $this->link_field;
    $id = $this->form->id;
    $dirname = $this->base . '/' . $id.'/';

    global $sqlconn;
    $result = mysqli_query($sqlconn, $q, MYSQLI_USE_RESULT);

    echo '<ul class="checkbox-list">';
    for ($i = 0; $row = mysqli_fetch_assoc($result); $i++)
      $this->render_file ($row[$this->id_field], $dirname . $row[$this->name_field]);
    echo '</ul>';
  }

  function render_file ($key, $fname)
  {
    global $_root;
    if ($this->link === FALSE)
      {
	FileField::render_file ($key, $fname);
	return;
      }

    $f = basename ($fname);
    $ext = $this->get_file_ext ($f);
    $nome = substr ($f, 0, strlen ($f) - strlen ($ext) - 1);

    echo '<li><input type="hidden" name="', $this->field, '-name[', $key, ']" ',
      'value="', htmlspecialchars ($f), '" />',
      '<input type="checkbox" name="', $this->field, '-keep[', $key, ']" id="',
      $this->id, '-keep-', $key,
      '" value="yes" checked="checked" />&nbsp;<img src="', $_root, '/images/',
      $ext, '.gif" alt="', $ext, ' attachment"> <a href="',
      $this->link, $key, '">',
      htmlspecialchars (str_replace ('_', ' ', $nome)), 
      '</a> <label for="', $this->id, '-keep-', $key, '">(',
      $ext, ' file, ', $this->file_size ($fname), ')</label></li>';
  }

}

class ManyManyField extends FormField
{
  var $bridge_table;
  var $label, $add_label;
  var $yes, $no;

  function __construct (&$form, $bridge_table, $field,
		        $other_table, $template, $label, $add_label)
  {
    parent::__construct ($form, $field);
    $this->bridge_table = $bridge_table;
    $this->label = $label;
    $this->add_label = $add_label;

    # Load all the rows from the database
    preg_match_all ('/@([0-9A-Za-z_]+)/', $template, $fields);
    $query = 'select ' . $field . ', ' .
	     implode (', ', array_unique ($fields[1])) .
	     ' from ' . $other_table;

    global $sqlconn;
    $result = mysqli_query($sqlconn, $query);
    $names = array ();
    while ($row = mysqli_fetch_assoc($result))
      $names[$row[$field]] = 
        preg_replace ('/@([0-9A-Za-z_]+)/e', "\$row['\\1']", $template);

    # Split them between the ones already associated, and the others
    if ($form->id === FALSE)
      {
	$this->yes = array ();
	$this->no = $names;
      }
    else
      {
        $query = 'SELECT %3$s.%4$s, MAX(IF(%2$s = %5$s, 1, 0)) AS in_list
		  FROM %3$s LEFT JOIN %1$s on %3$s.%4$s = %1$s.%4$s
		  GROUP BY %3$s.%4$s';
	$query = sprintf ($query, $bridge_table, $form->id_field,
			  $other_table, $field,
			  $form->id);

	$result = mysqli_query($sqlconn, $query);
	$this->yes = array ();
	$this->no = array ();
        while ($row = mysqli_fetch_row($result))
	  {
	    list ($id, $in_list) = $row;
	    if ($in_list)
	      $this->yes[$id] = $names[$id];
	    else
	      $this->no[$id] = $names[$id];
	  }
      }

    asort ($this->yes);
    asort ($this->no);
  }

  function render ()
  {
    if (count ($this->yes))
      {
	echo '<p>', $this->label, '</p><ul class="checkbox-list">';
	foreach ($this->yes as $id => $name)
	  {
	    echo '<li><input name="', $this->field, '[', $id,
		 ']" type="checkbox" id="', $this->field, '-', $id,
		 '" value="yes" checked="checked" /> ';
	    $this->label ($this->field . '-' . $id, $name);
	    echo '</li>';
	  }
	echo '</ul>';
      }

    if (count ($this->no))
      {
	echo '<p>';
	$this->label ($this->field, $this->add_label);
	echo '<select id="', $this->field, '" name="', $this->field, '_add">';
	echo '<option value="">---</option>';
	foreach ($this->no as $id => $name)
	  echo '<option value="', $id, '">', $name, '</option>';
	echo '</select></p>';
      }
  }

  function db_statement ()
  {
    return '';
  }

  function execute_post_insert ()
  {
    global $sqlconn;

    $added = $this->form->get_value ($this->field . '_add');
    $delete = 'delete from ' .  $this->bridge_table . ' where ' .
	      $this->form->id_field . ' = "' . mysqli_real_escape_string($sqlconn, $this->form->id) .
	      '" and not (' . $this->field . ' in (';

    $comma = '';
    foreach ($this->form->get_value ($this->field) as $id => $dummy)
      {
	$delete .= $comma . '"' . mysqli_real_escape_string($sqlconn, $id) . '"';
	$comma = ', ';
      }

    $delete .= '))';
    mysqli_query($sqlconn, $delete);

    if ($added != '')
      mysqli_query($sqlconn, 'insert into ' . $this->bridge_table . ' set ' .
		   $this->field . ' = "' . mysqli_real_escape_string($sqlconn, $added) . '", ' .
		   $this->form->id_field . ' = "' . mysqli_real_escape_string($sqlconn, $this->form->id) .
		   '"');
  }
}

class RadioField extends DBField
{
  var $default;
  var $list;

  function __construct (&$form, $field, $list, $default = '')
  {
    parent::__construct ($form, $field);
    $this->default = $default;
    $this->list = $list;
  }

  function render ()
  {
    $value = $this->form->get_value ($value, $this->default);

    foreach ($this->list as $k => $v)
      {
	$id = $this->id . '-' . $k;
        echo '<input
	  id="', $id, '" name="', $this->field, '" value="',
          htmlspecialchars ($k), '" type="radio" ';
	if ($k == $value)
	  echo 'checked="" ';
	echo '/>';
        $this->label ($id, $v);
	echo '<br />';
      }
  }
}

class SelectField extends DBField
{
  var $label, $default;
  var $list;

  function __construct (&$form, $field, $label, $list, $default = '')
  {
    parent::__construct ($form, $field);
    $this->label = $label;
    $this->default = $default;
    $this->list = $list;
  }

  function render ()
  {
    $value = $this->form->get_value ($this->field, $this->default);

    $this->label ($this->id, $this->label);
    echo '<select
	  id="', $this->id, '" name="', $this->field, '">';
    foreach ($this->list as $k => $v)
      {
        echo '<option value="', htmlspecialchars ($k), '" ';
	if ($k == $value)
	  echo 'selected="" ';
	echo '>', $v, '</option>';
      }
    echo '</select>';
  }
}

class Form
{
  var $id;
  var $id_field;
  var $table;
  var $fields;
  var $data;

  function __construct ($table, $id_field = 'id')
  {
    $this->table = $table;
    $this->id_field = $id_field;
    $this->fields = array ();
    if (isset ($_POST[$id_field]))
      $this->id = $_POST[$id_field];
    else if (isset ($_GET[$id_field]))
      $this->id = $_GET[$id_field];
    else
      $this->id = FALSE;

    global $sqlconn;

    if ($this->id !== FALSE && $this->table !== FALSE)
      {
        $q = 'select * from ' . $this->table . ' where ' . $this->id_field . '="' .
	  mysqli_real_escape_string ($sqlconn, $this->id) . '"';
        $result = mysqli_query($sqlconn, $q, MYSQLI_USE_RESULT);
        $this->data = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
      }
    else
      $this->data = array ();

    foreach ($_POST as $k => $v)
      $this->data[$k] = $v;
    foreach ($_FILES as $k => $v)
      $this->data[$k] = $v;
    foreach ($_GET as $k => $v)
      $this->data[$k] = $v;

    if ($this->id !== FALSE)
      new IDField ($this, $this->id_field, $this->id);
  }

  function render ()
  {
    $enctype = '';
    foreach ($this->fields as $f)
      if (is_subclass_of ($f, 'filefield'))
        $enctype = 'enctype="multipart/form-data" ';

    echo '<form method="post" ', $enctype, 'action="', $_SERVER['PHP_SELF'], '">';
    foreach ($this->fields as $f)
      $f->render ();

    echo '<input type="submit" value="Ok" /></form>';
  }

  function get_value ($f, $default = '')
  {
    if (isset ($_POST[$f]))
      return $_POST[$f];
    if (isset ($_FILES[$f]))
      return $_FILES[$f];
    if (isset ($_GET[$f]))
      return $_GET[$f];
    if (isset ($this->data[$f]))
      return $this->data[$f];
    else
      return $default;
  }

  function execute ($redirect = FALSE)
  {
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
      return;

    foreach ($this->fields as $f)
      if (!$f->validate ())
	{
	  $f->error = TRUE;
	  return;
	}

    if ($this->table !== FALSE)
      $this->execute_db ();
    foreach ($this->fields as $f)
      $f->execute_post_insert ();

    global $sqlconn;
    if ($redirect === FALSE)
      $redirect = '?' . $this->id_field . '=' . $this->id;;
    header ('Location: ' . $redirect);
    exit ();
  }

  function execute_db ()
  {
    global $sqlconn;
    if ($this->id !== FALSE)
      {
        $q = 'update ' . $this->table . ' set ';
        $post = ' where ' . $this->id_field . '="' . mysqli_real_escape_string($sqlconn, $this->id) . '"';
      }
    else
      {
        $q = 'insert into ' . $this->table . ' set ';
        $post = '';
      }

    $comma = '';
    foreach ($this->fields as $f)
      if (isset ($_POST[$f->field]))
        {
	  $sql = $f->db_statement ();
          if (!empty ($sql))
	    {
	      $q .= $comma . $sql;
	      $comma = ', ';
	    }
        }

    if ($comma != '')
      mysqli_query($sqlconn, $q . $post) or die ($q . ' ' . mysqli_error($sqlconn));

    if ($this->id === FALSE)
      $this->id = mysqli_insert_id($sqlconn);
  }
}

?>
