class scanner{
 function __construct() {}

 function scanProcess(){
  if(isset($_POST['url'])){
   $ret = array();
   $this->directoryscan($ret, $_POST['url']);
   $contents ="
      <tr bgcolor='#413B3B'>
       <td align=center><font color=#3cbddd>PathFile</font></td>
       <td align=center><font color=#3cbddd>Function</font></td>
      </tr>";
   
   foreach ($ret as $key => $value){
    $contents .= "<tr bgcolor='#191919'><td width=30%><a href='?viewfile=".$key."' target=_blank><font color=#3cbddd>".$key."</font></a></td><td><font color=#3cbddd>".$this->string_fromArray($value,",")."</font></td></tr>";
   }
   return  $contents;     
  }
  

 }
  function string_fromArray($list,$diff){
  $stack = $list;
  $separator = $diff;
  $string = "";
  for($i=0;$i<sizeof($stack); $i++) {
   if(strlen($string)==0) {
    $string .= $stack[$i];
   }else {
    $string .= $separator." ".$stack[$i];
   }
  }
  return $string;
 }
  function path_strip($path) {
  $raw = array();
 
  $path = $this->setSeparator($path);

  if($this->str_startsWith(".".$this->getSeparator(), $path)) {
   $ppath = explode($this->getSeparator(), dirname(__FILE__));
   $raw = $this->path_strip_pdp($ppath, $raw);
  }
  
  $tpath = explode($this->getSeparator(), $path);
  $raw = $this->path_strip_pdp($tpath, $raw);
  
  if(sizeof($raw) == 0)
   $raw[] = "";
    
  return $raw;
 }
 function str_startsWith($needle, $string) {
  $length = strlen($needle);
  return (substr($string, 0, $length) === $needle);
 }

 function str_endsWith($needle, $string) {
  $pos  = strlen($string) - strlen($needle);
  return (substr($string, $pos) === $needle);
 }
 function path_strip_pdp($path, $stack) {
  for($i=($this->getOs()== 1? 0:1); $i<sizeof($path); $i++) {
   if($path[$i] != "" && $path[$i] != ".") {
    if($path[$i] == "..") {
     if(sizeof($stack) > ($this->getOs()== 1? 1:0))
      array_pop($stack);
    }else
     $stack[] = $path[$i];
   }
  }
  
  return $stack;
 }
 function setSeparator($path) { 
  if($this->getOs() == 1)
    return str_replace("/", "\\", $path);
  else
     return str_replace("\\", "/", $path);
 }
 function getSeparator() { 
  if($this->getOs() == 1)
   return "\\";
  else
   return "/";
 }
 function getOs() {
  if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
   return 1;
  else
   return 0;
 }
  function str_fromArray($stack, $delimiter="", $type="") {
  $string = "";
  for($i=0; $i<sizeof($stack); $i++)
   switch($type) {
    case "path":
     if ($this->getOs() == 1) {
      if($i<sizeof($stack)-1)
       $string .= $stack[$i].$delimiter;
      else 
       $string .= $stack[$i];
     }else {
      $string .= $delimiter.$stack[$i];
     }
     break;
    default:
     if(strlen($string) == 0)
      $string .= $stack[$i];
     else
      $string .= $delimiter.$stack[$i];
   }
   
  return $string;
 }

 function array_add(&$array, $input) {
  if(is_array($array)) {
   if(!in_array($input, $array))
    array_push($array, $input);
  }
 }
 
 function directoryscan(&$foundMatch, $url){
  $thDir = $url;
  $contents = "";

  $thDir = $this->str_fromArray($this->path_strip($thDir),$this->getSeparator(), "path");

  if(is_dir($thDir)) {

   $handle = opendir($thDir);
   $list = array();
   $dir = array();
   $file = array();
   while(false !== ($entry = readdir($handle))){
    if(is_dir($entry))
     array_push($dir, $entry);
    else
     array_push($file, $entry);
   } 
   sort($dir);
   sort($file);

   $list = array_merge($dir, $file);

   closedir($handle);
   foreach ($list as $filsscan){

    if($thDir.$this->getSeparator().$filsscan == __FILE__) {
     continue;
    }

    if(is_dir($thDir.$this->getSeparator().$filsscan)) {

     if($filsscan != "." && $filsscan != "..") {
      $this->directoryscan($foundMatch, $thDir.$this->getSeparator().$filsscan);
      
     }
    }else  {

     $ext_this = pathinfo($thDir.$this->getSeparator().$filsscan, PATHINFO_EXTENSION);
     if($ext_this == "php" || $ext_this == "pl" || $ext_this == "py" || $ext_this == "nzri" || $ext_this == "izo" ||
       $ext_this == "cgi" || $ext_this == "htaccess") {
     
      $file = fopen ($thDir.$this->getSeparator().$filsscan,"r");
      $funcfound = array();
      
      while(!feof($file)){ 
        $contents = fgets($file);
       
       if(preg_match("/(|[\;\(\{\s\.\,])copy\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound, "copy");
        
       }
       if(preg_match("/(|[\;\(\{\s\.\,])move\_uploaded\_file\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "move_uploaded_file");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])passthru\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "passthru");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])shell\_exec\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "shell\_exec");
       } 
       if(preg_match("/(|[\;\(\{\s\.\,])exec\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "exec");
       } 
        if(preg_match("/(|[\;\(\{\s\.\,])base64\_decode\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound, "base64_decode");
       } 
       if(preg_match("/(|[\;\(\{\s\.\,])eval\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "eval");
       } 
       if(preg_match("/(|[\;\(\{\s\.\,])proc\_open\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "proc_open");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])system\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "system");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])curl\_exec\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "curl_exec");
       } 
       if(preg_match("/(|[\;\(\{\s\.\,])popen\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound,  "popen");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])curl\_multi\_exec\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound, "curl_multi_exec");
       } 
       if(preg_match("/(|[\;\(\{\s\.\,])rename\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound, "rename");
       } 
       if(preg_match("/(|[\;\(\{\s\.\,])parse\_ini\_file\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound, "parse_ini_file");
       } 
       if(preg_match("/(|[\;\(\{\s\.\,])\$\_FILES\s*?[\[].*?[\]]\s*?[\.\,\;\}\_]/i",$contents)){
        $this->array_add($funcfound, "\$_FILES");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])show\_source\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound, "show_source");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])fopen\s*?[\(].*?[\)]\s*?[\.\,\;\{\}\_]/i",$contents)){
        $this->array_add($funcfound, "fopen");
       }
       if(preg_match("/(|[\;\(\{\s\.\,])\$\_COOKIE\s*?[\[].*?[\]]\s*?[\.\,\;\}\_]/i",$contents)){
        $this->array_add($funcfound, "\$_COOKIE");
       }
       if(preg_match("/.*\s*AddType\s+application\/x\-httpd\-php.*/i",$contents)) {
        $this->array_add($funcfound, "AddType application/x-httpd-php");
       }
       if(preg_match("/.*\s*AddType\s+application\/x\-httpd\-cgi.*/i",$contents)) {
        $this->array_add($funcfound, "AddType application/x-httpd-cgi");
       }
       if(preg_match("/.*\s*AddType\s+application\/x\-httpd\-perl.*/i",$contents)) {
        $this->array_add($funcfound,"AddType application/x-httpd-perl");
       }
       if(preg_match("/.*\s*AddHandler\s+cgi\-script.*/i",$contents)) {
        $this->array_add($funcfound, "AddHandler cgi-scrinpt");
       } 
       
      } 
      
      if(sizeof($funcfound) > 0) {
       sort($funcfound);
       $foundMatch[$thDir.$this->getSeparator().$filsscan] = $funcfound; 
      } 
      fclose($file); 
     }

    }
   }
  }
  return $foundMatch;
 }
 function viewSource($path){
  $content = file_get_contents($path);
  return "<pre>".str_replace("<", "&lt;", $content)."</pre>";
 } 
}
