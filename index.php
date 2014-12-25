
include("function.php");

$a = new scanner();

echo "
  <link rel='stylesheet' type='text/css' href='./css/style.css' />
  <body text='white' bgcolor='#111111'>
   
  <center><table class=hov style='border-collapse: separate; background-color: #2E2E2E;border: solid 1px; border-radius: 5px;width:1300px;'>
      <tr>
       <td>
        <form action=?scan method=post>
         <center><input type=text name='url'  style='border: 1px solid;background-color:transparent;color:#99CCFF;border-radius: 5px' size=100  value='".dirname(__FILE__)."'></center>
        </form> 
       </td>
      </tr>    
      <tr>
       <td style='border:solid 1px; border-radius: 5px;'>
        <table class=hov style='border-collapse: separate; background-color: #2E2E2E;border-radius: 5px;width:100%;height:80%;align:center' id= ''>

        ".$a->scanProcess()."
        </table> 
       </td>
       <td></td>
      </tr> 
    </table></center><br><br><br>        
  </body>";
  echo (isset($_GET['viewfile']) ? $a->viewSource($_GET['viewfile']) : '');

