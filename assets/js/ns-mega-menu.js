(function(){
  // キーボード操作・モバイル開閉（シンプル制御）
  var navs = document.querySelectorAll('.nsmm');
  if(!navs.length) return;

  navs.forEach(function(nav){
    // スマホ：トップをクリックで開閉
    nav.addEventListener('click', function(e){
      if (window.matchMedia('(max-width: 980px)').matches) {
        var link = e.target.closest('.nsmm-top > .nsmm-link');
        if (link) {
          e.preventDefault();
          var li = link.parentElement;
          li.classList.toggle('nsmm-open');
        }
      }
    });

    // エスケープで全閉じ
    nav.addEventListener('keydown', function(e){
      if (e.key === 'Escape') {
        nav.querySelectorAll('.nsmm-open').forEach(function(opn){ opn.classList.remove('nsmm-open'); });
      }
    });
  });
})();
