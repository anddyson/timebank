function ValidEmail(email)
{
  //very basic test for a valid email. Use jQuery validation for preference instead of this
  var regEx = /^[\S]+@[\S]+\.[\S]+$/;
  return regEx.test(email);
}

//gets the date a certain time before/after today's date, depending on the selection made
function getSelectedDate(dateOption, direction)
{
    dat = new Date();
    switch (dateOption)
    {
        case "1":        // 2 weeks
            if  (direction == "past") { dat.setDate(dat.getDate() - 14); }
            else if (direction == "future") { dat.setDate(dat.getDate() + 14) }
            break;
        case "2":        // 4 weeks
            if  (direction == "past") { dat.setDate(dat.getDate() - 28); }
            else if (direction == "future") { dat.setDate(dat.getDate() + 28); }
            break;
        case "3":        // 3 months
            if (direction == "past") { dat.setMonth(dat.getMonth() - 3); }
            else if (direction == "future") { dat.setMonth(dat.getMonth() + 3); }
            break;
        case "4":        // 6 months
            if  (direction == "past") { dat.setMonth(dat.getMonth() - 6); }
            else if (direction == "future") { dat.setMonth(dat.getMonth() + 6); }
            break;
        case "5":        // 12 months
            if  (direction == "past") { dat.setFullYear(dat.getFullYear() - 1); }
            else if (direction == "future") { dat.setFullYear(dat.getFullYear() + 1); }
            break;
        case "6":        // anytime / never
            return null;
            break;
    }
    
    return dat;
}

//format a date into a string in universal date format (yyyy-mm-dd)
function formatDateUniversal(d) {

    var strDay = d.getDate().toString();
    if (strDay.length == 1) strDay = "0" + strDay; //padding if day is <10
    var strMonth = (d.getMonth() + 1).toString(); //Months are zero based
    if (strMonth.length == 1) strMonth = "0" + strMonth;
    var strYear = d.getFullYear();
    return (strYear + "-" + strMonth + "-" + strDay);
}

window.size = function()
{
   var w = 0;
   var h = 0;

   //IE
   if(!window.innerWidth)
   {
      //strict mode
      if(!(document.documentElement.clientWidth == 0))
      {
         w = document.documentElement.clientWidth;
         h = document.documentElement.clientHeight;
      }
      //quirks mode
      else
      {
         w = document.body.clientWidth;
         h = document.body.clientHeight;
      }
   }
   //w3c
   else
   {
      w = window.innerWidth;
      h = window.innerHeight;
   }
   return {width:w,height:h};
}

window.center = function()
{
   var hWnd = (arguments[0] != null) ? arguments[0] : {width:0,height:0};

   var _x = 0;
   var _y = 0;
   var offsetX = 0;
   var offsetY = 0;

   //IE
   if(!window.pageYOffset)
   {
      //strict mode
      if(!(document.documentElement.scrollTop == 0))
      {
         offsetY = document.documentElement.scrollTop;
         offsetX = document.documentElement.scrollLeft;
      }
      //quirks mode
      else
      {
         offsetY = document.body.scrollTop;
         offsetX = document.body.scrollLeft;
      }
   }
   //w3c
   else
   {
      offsetX = window.pageXOffset;
      offsetY = window.pageYOffset;
   }

   _x = ((this.size().width-hWnd.width)/2)+offsetX;
   _y = ((this.size().height-hWnd.height)/2)+offsetY;

   return{x:_x,y:_y};
}
