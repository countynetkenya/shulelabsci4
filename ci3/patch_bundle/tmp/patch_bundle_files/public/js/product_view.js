(function(){
  function fmtMonth(d){ var x=new Date(d); return x.getFullYear()+'-'+String(x.getMonth()+1).padStart(2,'0'); }
  async function loadProductMovement(productId, params){
    const qs = new URLSearchParams(params).toString();
    const res = await fetch('/productapi/movement_series/'+productId+'?'+qs, {credentials:'same-origin'});
    if(!res.ok){ throw new Error('Failed to load movement series'); }
    return res.json();
  }
  window.renderMovementChart = async function(productId, params){
    try{
      const data = await loadProductMovement(productId, params||{});
      const labels = data.series.map(s=>fmtMonth(s.month));
      var el = document.getElementById('movementChart');
      if(!el) return;
      var html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Month</th><th>Purchases</th><th>Sales</th><th>Adjustments</th><th>Non-billable</th></tr></thead><tbody>';
      data.series.forEach(function(s,i){
        html += '<tr><td>'+fmtMonth(s.month)+'</td><td>'+(s.purchases||0)+'</td><td>'+(s.sales||0)+'</td><td>'+(s.adjustments||0)+'</td><td>'+(s.nonbillable||0)+'</td></tr>';
      });
      html += '</tbody></table></div>';
      el.innerHTML = html;
    }catch(e){ console.error(e); }
  };
})();