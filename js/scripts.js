function logout(){
  $.ajax({url: "modulo/logout.php"});
  window.localStorage.clear();  
}
$(document).ready(function(){
    $("#logout").click(function(e){
         e.preventDefault();
         logout();
         window.location.reload();
    });
    $.ajax({
            url: "modulo/is_logged.php",
            crossDomain: true,
            dataType: 'text',
            async: false,
            success:function(response){
                var Storage=window.localStorage.getItem('token');
                if(response=='false' && Storage === null){
                    console.log(' is logged, no sesion, no token view login');
                    $('#logged').addClass('d-none');
                    $('#logged').html('');
                    $('#login').removeClass('d-none');  
                    $('body').addClass('bg-blue');
                }else{
                    if(Storage === null){
                        console.log(' is logged, no sesion, no token add token');
                        window.localStorage.setItem('token',response);
                    }else if(Storage!==response){
                        console.log(' is logged, si sesion, dist token clear storage')
                        window.localStorage.clear();
                        window.location.href = "modulo/login.php?login=true";
                    }
                }
            }
    });
    
    var Storage=window.localStorage.getItem('token');
    var userStorage=window.localStorage.getItem('user');
    var user;
    if(Storage !== null){
        console.log('user load, token exist' + typeof(Storage));
        if(userStorage !== null){
            console.log('userload, user exist ');
        }else{
            console.log('userload, user noexist');
            $.ajax({
                url: "modulo/login.php?logged="+Storage,
                crossDomain: true,
                dataType: 'json',
                async: false,
                success:function(response){
                    console.log('userload, user load storage');
                    window.localStorage.setItem('user',JSON.stringify(response));
                }
            });
             
        }
        userStorage=window.localStorage.getItem('user'); 
        user=JSON.parse(userStorage); 
        console.log(user);
        if((user.data.vatsim.subdivision.id=="ARG"||user.data.personal.country.id=="AR")&&user.data.vatsim.division.id=="SAM"){
            if(user.data.vatsim.rating.id<2){
                logout();
                $('#acceso').html('<h1 class="text-warning">Acceso denegado</h1>'); 
                $('#login').removeClass('d-none');  
                $('body').addClass('bg-blue'); 
                $('#logged').html('');
                
            }else{
                $('.name').text(user.data.personal.name_full);
                $('#logged').removeClass('d-none');
                $('#login').addClass('d-none');  
                $('body').removeClass('bg-blue');
                    
            } 
       }else{
          logout();  
          $('#acceso').html('<h1 class="text-warning">Acceso denegado</h1>'); 
          $('#login').removeClass('d-none');  
          $('body').addClass('bg-blue');
          $('#logged').html(''); 
        }
    
    }
});