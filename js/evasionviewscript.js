function clicou(){
    console.log("Foi!");
}


                function move(evt){
                    var a = document.getElementsByClassName("chart-content");
//                      console.log(evt.style.backgroundColor = "blue");                
                        var nome = evt.attributes.name.value;
                        evt.style.opacity = 0.5;
//                        evt.innerHTML = "<strong>"+nome+"</strong>";
                        console.log(nome);                
                }                
                
                function moveover(evt){
                    var a = document.getElementsByClassName("chart-content");
//                    console.log(evt.style.backgroundColor = "blue");
//                    evt.innerHTML = "";
                    console.log(evt.style.opacity = "");                
                }
                
                function clickbar(evt){
                    var parsedUrl = new URL(window.location.href);
                    parsedUrl.searchParams.set('userinfo',evt.attributes.name.value);
                    window.location.href = parsedUrl;                    
                    console.log(evt.attributes.name.value);                
                }