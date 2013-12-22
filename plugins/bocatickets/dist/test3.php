<html>
   <head><title>jZebra Demo</title>    
   <script type="text/javascript">
      function useDefaultPrinter() {
         var applet = document.jzebra;
         if (applet != null) {
            // Searches for default printer
            applet.findPrinter();
         }
       }
      
      function jzebraReady() {
          // Change title to reflect version
          var applet = document.jzebra;
          var title = document.getElementById("title");
          if (applet != null) {
              title.innerHTML = title.innerHTML + " " + applet.getVersion();
              document.getElementById("content").style.background = "#F0F0F0";
          }
      }      

   </script>
   </head>
   
   <body id="content" bgcolor="#FFF380">
   <?php 
$output = "<PP><RC5,5><LT4><BX380,880><RC50,75><HW1,1><F2>Seat<RC74,75><HW2,2><F2>22<RC68,34><LT2><BX36,140><RC138,75><HW1,1>Row<RC162,75><HW2,2>A<RC156,34><LT2><BX36,140><RC230,70><HW1,1>Price<RC254,45><HW2,2>$15.00<RC248,34><LT2><BX36,140><RC333,45><F3><HW1,1>#63661<RC328,34><LT2><BX36,140><RC30,220><LT2><BX200,620><F2><HW2,1><RC40,230>    The Harrisburg Christian Performing Arts Center Presents<F3><HW2,1><RC87,230>         Sound of Music<F9><HW1,1><RC165,230>         Sunday 30 June 2013 02:30 PM<RC195,234>             Class 1 Red Seating<RC252,350><F2><HW2,1>Harrisburg Christian Performing Arts Center <RC285,370><HW2,2><F1>1000 S. Eisenhower Blvd. <RC305,370>Middletown, PA 17057 <RC325,370>Phone 717-939-9333 <RC345,370>No Refunds or Exchanges.<F2><HW1,1><RR><RC40,1036>Sound of Music<RC40,1018>Sunday 30 June 2013 02:30 PM<RC40,1000>#63661<RC40,982>Online payment<RC40,964>Class 1<RC40,946>Seat 22, Row A<NR><p>";
   ?>
   <script type="text/javascript">
      function print() {
         var applet = document.jzebra;
         if (applet != null) {
            // Searches for locally installed printer with "Officejet" in the name
            applet.findPrinter("Officejet_6600");
            // Send characters/raw commands to applet using "append"
            // Hint:  Carriage Return = \r, New Line = \n, Escape Double Quotes= \"
            applet.append( <?php echo '"'.$output.'"'; ?> );
            // Send characters/raw commands to printer
            applet.print();
         }
      }
   </script>
   
   <h1 id="title">jZebra Sample Applet</h1>
   <h2>All Printers</h2>
   <applet name="jzebra" code="jzebra.PrintApplet.class" archive="./jzebra.jar" width="5px" height="5px">
      <!-- Note:  It is recommended to use applet.findPrinter() instead for ajax heavy applications -->
      <param name="printer" value="Officejet_6600">
      <!-- Optional, these "cache_" params enable faster loading "caching" of the applet -->
      <param name="cache_option" value="plugin">
      <!-- Change "cache_archive" to point to relative URL of jzebra.jar -->
      <param name="cache_archive" value="./jzebra.jar">
      <!-- Change "cache_version" to reflect current jZebra version -->
      <param name="cache_version" value="1.4.8.0">
   </applet>
   <input type=button onClick="print()" value="Print" /><br />     
   </body>
</html>
