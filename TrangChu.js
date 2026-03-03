window.onload = function() {
    const passInput = document.querySelector('.input-star');
    const eyeZone = document.querySelector('.conmat');
     const mat1 = document.querySelector('.mat1');
     const mat2 = document.querySelector('.mat2');
     const mattat = document.querySelector('.mattat');
     const mathien = document.querySelector('.mathien');
 
    if (eyeZone && passInput) {
        eyeZone.addEventListener('click', function() {
            this.classList.toggle('active');

            if (passInput.type === 'password') {
                passInput.type = 'text';
                mat1.classList.remove('hien');
                mat1.classList.add('tat');
                mat2.classList.remove('tat');
                mat2.classList.add('hien');
            } else {
                passInput.type = 'password';
              
                mat1.classList.remove('tat');
                  mat1.classList.add('hien');

                mat2.classList.remove('hien');
                mat2.classList.add('tat');
                
            }
        });
    }
};