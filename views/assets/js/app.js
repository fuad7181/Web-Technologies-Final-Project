
function ajaxLogin(e){
  e.preventDefault();
  let f=e.target;
  fetch('api/login.php',{method:'POST',body:new FormData(f)})
  .then(r=>r.json()).then(d=>{
    if(d.ok) location.href=d.redirect;
    else alert('Invalid Login');
  });
  return false;
}
