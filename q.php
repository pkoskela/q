<?php
class q {
 private static $_root = null;
 private static $_stack = null;
 private $elements = null;
 private $name = null;
 public function __construct($name) { $this->elements = array(); $this->name = $name; }
 public function out() { foreach($this->elements as $e) if (is_a($e, 'q')) $e->out(); else echo $e; }
 public function append($e) { array_push($this->elements, $e); }
 public function clear() { $this->elements = array(); }
 public function find($n)
 {
  if ($this->name === $n) return $this;
  foreach($this->elements as $e)
  {
   if (!is_a($e, 'q')) continue;
   if (($f = $e->find($n)) !== null) return $f;
  }
  return null;
 }
 public static function q($b = "")
 {
  if (q::$_root === null)
  {
   q::$_root = new q('_root');
   q::$_stack = array('_root');
  }

  $c = ob_get_contents();
  @ob_end_clean();

  q::$_root->find(q::$_stack[count(q::$_stack)-1])->append($c);

  if ($b === "") array_pop(q::$_stack);

  if (count(q::$_stack) == 0)
  {
   q::$_root->out();
   return;
  }

  $previous_content = "";

  if ($b !== "")
  {
   $a = q::$_root->find($b);
   if ($a === null) q::$_root->find(q::$_stack[count(q::$_stack)-1])->append(new q($b));
   if ($a !== null)
   {
    ob_start();
    $a->out();
    flush();
    $previous_content = ob_get_contents();
    @ob_end_clean();
    $a->clear();
   }
   array_push(q::$_stack, $b);
  }

  ob_start();

  return $previous_content;
 }
};
function q($b = "") { return q::q($b); }

ob_start();
register_shutdown_function('q');

if (php_sapi_name() !== 'cli') return;
?>
<html>
<head>
<title><?php q('title');?>original title<?php q();?></title>
<?php q('head'); q(); ?>
</head>
<body>
<?php q('body'); q(); ?>
</body>
</html>
<?php

/* replace 'title' block with text "new title" */
q('title'); echo "new title"; q();

/* append a new script tag to the 'head' block */
echo q('head'); echo "<script>alert(\"hello\");</script>\n"; q();

/* append another script tag to the 'head' block */
echo q('head'); echo "<script>alert(\"world\");</script>\n"; q();

/* prepend link tag to the 'head' block */
$head = q('head'); echo "<link rel=\"stylesheet\"/>\n"; echo $head; q();

/* overwrite 'body' block with text */
q('body'); echo "body text\n"; q();
?>
