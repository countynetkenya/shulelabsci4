(function(){
  // --- Shared utilities ------------------------------------------------------
  function fmtMonth(value){
    var date = new Date(value);
    if (isNaN(date.getTime())) {
      return value;
    }
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    return year + '-' + month;
  }

  function showState(el, type){
    if(!el) return;
    var markup = '';
    switch(type){
      case 'loading': markup = '<div class="movement-chart__state"><span class="movement-chart__spinner"></span> Loading movement…</div>'; break;
      case 'empty':   markup = '<div class="movement-chart__state">No movement recorded for the selected range.</div>'; break;
      case 'error':   markup = '<div class="movement-chart__state text-danger">Unable to load movement data. Please try again.</div>'; break;
      default:        markup = '';
    }
    el.innerHTML = markup;
  }

  function updateStatus(state){
    var statusEl = document.getElementById('movementChartStatus');
    if(!statusEl) return;

    statusEl.classList.remove('text-danger');
    statusEl.style.display = '';

    switch(state){
      case 'loading':
        statusEl.innerHTML = '<span class="movement-chart__spinner"></span> Loading movement…';
        break;
      case 'empty':
        statusEl.textContent = 'No movement recorded for the selected range.';
        break;
      case 'error':
        statusEl.textContent = 'Unable to load movement data. Please try again.';
        statusEl.classList.add('text-danger');
        break;
      default:
        statusEl.textContent = '';
        statusEl.style.display = 'none';
        break;
    }
  }

  function destroyChart(){
    if (window.movementChartInstance && typeof window.movementChartInstance.destroy === 'function'){
      window.movementChartInstance.destroy();
    }
    window.movementChartInstance = null;
    // Back-compat name if used elsewhere
    window.movementChart = null;
  }

  function escapeHtml(str){
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatNumber(value){
    var num = Number(value);
    if(!isFinite(num)) return '0';
    return num.toLocaleString(undefined, { maximumFractionDigits: 2 });
  }

  // --- Data helpers ----------------------------------------------------------
  async function loadProductMovement(productId, params){
    const qs  = new URLSearchParams(params || {}).toString();
    const url = '/productapi/movement_series/' + productId + (qs ? ('?' + qs) : '');
    const res = await fetch(url, {credentials: 'same-origin'});
    if(!res.ok){
      throw new Error('Failed to load movement series');
    }
    return res.json();
  }

  function mapSeries(series){
    var labels = [];
    var stockIn = [];
    var stockOut = [];

    series.forEach(function(point){
      var label        = fmtMonth(point.month);
      var purchases    = Number(point.purchases)    || 0;
      var sales        = Number(point.sales)        || 0;
      var adjustments  = Number(point.adjustments)  || 0;
      var nonbillable  = Number(point.nonbillable)  || 0;
      var adjustmentIn  = adjustments > 0 ? adjustments : 0;
      var adjustmentOut = adjustments < 0 ? Math.abs(adjustments) : 0;

      labels.push(label);
      stockIn.push(purchases + adjustmentIn);
      stockOut.push(sales + nonbillable + adjustmentOut);
    });

    return { labels: labels, stockIn: stockIn, stockOut: stockOut };
  }

  function hasValues(values){
    return values.some(function(v){
      return v !== null && v !== undefined && Number(v) > 0;
    });
  }

  // --- Render entrypoint -----------------------------------------------------
  window.renderMovementChart = async function(productId, params){
    var canvas = document.getElementById('movementChartCanvas');
    if(!canvas || !productId){
      return;
    }

    updateStatus('loading');
    destroyChart();

    try{
      const payload = await loadProductMovement(productId, params || {});
      const series  = payload && Array.isArray(payload.series) ? payload.series : [];

      if(!series.length){
        updateStatus('empty');
        return;
      }

      const mapped  = mapSeries(series);
      const hasData = hasValues(mapped.stockIn) || hasValues(mapped.stockOut);

      if(!hasData){
        updateStatus('empty');
        return;
      }

      var ctx = canvas.getContext('2d');

      // Use modern Chart.js API (v3+). If your project uses v2, adjust scales accordingly.
      window.movementChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: mapped.labels,
          datasets: [{
            label: 'Stock In',
            data: mapped.stockIn,
            backgroundColor: 'rgba(40, 167, 69, 0.6)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
          },{
            label: 'Stock Out',
            data: mapped.stockOut,
            backgroundColor: 'rgba(220, 53, 69, 0.6)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(ctx){
                  var v = ctx.parsed.y;
                  return ctx.dataset.label + ': ' + formatNumber(v);
                }
              }
            },
            legend: { display: true }
          }
        }
      });

      updateStatus('ready');
    }catch(e){
      console.error(e);
      updateStatus('error');
    }
  };
})();
