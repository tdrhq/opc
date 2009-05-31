function toggledisplay (id) { 

   var sem = document.getElementById(id); 
   
   if (sem.className=="shown") { 
      sem.className="hid"; 
   } 
   else { 
      sem.className="shown"; 
   } 
} 



function clearDefault(el) {
  if (el.defaultValue==el.value) el.value = ""
} 
function mouseover(id)
{
var obj = document.getElementById(id);
obj.className="bordered";
}
function mouseout(id)
{
var obj = document.getElementById(id);
obj.className="resting";
}
function uncryptmail(s) 
{
 s = s.replace(/AT/,'@');
 s = s.replace(/DOT/, '.');
 s = s.replace(/DOT/,'.');
 s = s.replace(/DOT/,'.');
 for(var i=0;i<10;i++)
  s = s.replace(/ /,'');
 return "<a href=\"mailto:" +s+ "\">"+s+"</a>" ;
}
