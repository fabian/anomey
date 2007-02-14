function setQuestions(){
	var links = document.getElementsByTagName("A");
	for (i=0; i<links.length; i++){
		var link = links.item(i);
		if(link.getAttribute("class")){
			if (link.getAttribute("class").indexOf("delete")!=-1){
				link.onclick=function(){
					return confirm("Deleting can't be undone!");
				}
			}
		}
	}
	
	var inputs = document.getElementsByTagName("INPUT");
	for (i=0; i<inputs.length; i++){
		var input = inputs.item(i);
		if(input.getAttribute("class") && input.getAttribute("type")){
			if (input.getAttribute("class").indexOf("delete")!=-1 && input.getAttribute("type") == "submit"){
				input.onclick=function(){
					return confirm("Deleting can't be undone!");
				}
			}
		}
	}
}

window.onload=setQuestions;