/*
Modal java script
*/
function open_modal(){
	$("#basic-modal-content").modal({onClose: function (dialog) {
			$("#basic-modal-content").html = '';
			$.modal.close();	
		}});
}
/*
End of modal javascript
*/

function select_gender(el,link){
	//var prev_html = el.parentNode.innerHTML;
	//var new_html = "<a class='btn' onclick='javascript:get_result(this.innerHTML,\""+link+"\")'>Men Topwear</a>&nbsp&nbsp<a class='btn' onclick='javascript:get_result(this.innerHTML,\""+link+"\")'>Women Topwear</a>";
	//document.getElementById('basic-modal-content').innerHTML =  new_html;
	get_result('Women Topwear',link);
  open_modal();
}

function get_result(val,link){
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    var data = jQuery.parseJSON(xmlhttp.responseText);
    var new_html = "<!-- Modal content goes here --><div style='height:300px;overflow:auto'>";
    for(var i=0;i<data.length;i++){
    	new_html += "<img style='padding:5px;max-height:150px; max-width:100px' src='http://www.beta.vindowshop.com"+data[i]+"'>";
    }
    new_html += '</div>';
    document.getElementById("basic-modal-content").innerHTML=new_html;
    }
  }
xmlhttp.open("POST","http://vindowshop.com:9999/fetchprod",true);
xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
xmlhttp.send('["'+link+'","'+val+'",600,600,0,0,100,100]');
}


function lights_in(el){
	el.setAttribute('style','opacity:1.0;position: absolute; z-index: 1; top: 15px; right: 30px; max-height:40px');
}

function lights_out(el){
	el.setAttribute('style','opacity:0.4;position: absolute; z-index: 1; top: 15px; right: 30px; max-height:40px');
}


function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return i;
    }
    return false;
}