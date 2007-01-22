function setQuestions(){
		links = document.getElementsByTagName("A");
		for (i=0; i<links.length; i++){
			link = links.item(i);
			if(link.className){
				if (link.className.indexOf("delete")!=-1){
					link.onclick=function(){
						return confirm("Deleting can't be undone!");
					}
				}
			}
		}
}

window.onload=setQuestions;