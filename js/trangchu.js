// ── Star field
(function(){
  const sf = document.getElementById('starfield');
  for(let i=0;i<120;i++){
    const s = document.createElement('div');
    s.className = 'star';
    const size = Math.random()*2.5+0.5;
    s.style.cssText = `
      width:${size}px;height:${size}px;
      left:${Math.random()*100}%;top:${Math.random()*100}%;
      --dur:${2+Math.random()*4}s;
      --minO:${0.1+Math.random()*0.2};
      --maxO:${0.5+Math.random()*0.5};
      animation-delay:${Math.random()*4}s;
    `;
    sf.appendChild(s);
  }
})();

// ── Intersection Observer for fade-in
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

// ── Filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', function(){
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});

// ── Countdown timer
let total = 8*3600 + 34*60 + 22;
function updateCountdown(){
  total = Math.max(0, total-1);
  const h = Math.floor(total/3600);
  const m = Math.floor((total%3600)/60);
  const s = total%60;
  document.getElementById('cd-h').textContent = String(h).padStart(2,'0');
  document.getElementById('cd-m').textContent = String(m).padStart(2,'0');
  document.getElementById('cd-s').textContent = String(s).padStart(2,'0');
}
setInterval(updateCountdown, 1000);

// ── Add to cart
document.querySelectorAll('.btn-add').forEach(btn => {
  btn.addEventListener('click', function(){
    const badge = document.querySelector('.cart-badge');
    badge.textContent = parseInt(badge.textContent) + 1;
    this.textContent = '✓ Đã thêm!';
    this.style.background = '#059669';
    setTimeout(() => { this.textContent = '🛒 Thêm vào giỏ'; this.style.background = ''; }, 1500);
  });
});
document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', function() {

    // Xóa active ở tất cả link
    document.querySelectorAll('.nav-links a')
      .forEach(a => a.classList.remove('active'));

    // Thêm active cho link vừa click
    this.classList.add('active');

  });
});