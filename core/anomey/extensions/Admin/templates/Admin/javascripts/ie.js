startList = function(){
	if (document.all&&document.getElementById){
		navRoot = document.getElementById("mainNavigation");
		checkNodeList(navRoot);
		
		navRoot = document.getElementById("userInfos");
		checkNodeList(navRoot);
	}
}

function checkNodeList(navRoot){
	nodeList = document.getElementsByTagName("LI");

	for(i=0; i<nodeList.length; i++){
		node = nodeList[i];
		if (node.nodeName=="LI"){
			node.onmouseover=function(){
				this.className+=" over";
			}
			node.onmouseout=function(){
				this.className=this.className.replace(" over", "");
			}
		}
	}
}

window.onload=startList;