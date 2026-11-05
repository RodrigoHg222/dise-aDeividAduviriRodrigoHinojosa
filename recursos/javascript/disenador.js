(() => {
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const message = $('#actionMessage');
  const preview = $('#garmentPreview');
  const stage = $('#previewStage');
  let zoom = 1;

  function selectOne(selector, element, className = 'active') {
    $$(selector).forEach(item => item.classList.toggle(className, item === element));
  }
  $$('.category-tab').forEach(button => button.addEventListener('click', () => {
    selectOne('.category-tab', button, 'selected');
    $$('.model-group').forEach(group => { group.hidden = group.dataset.group !== button.dataset.category; });
    $$('.model-card').forEach(item => item.classList.remove('chosen'));
    const first = $(`.model-group:not([hidden]) .model-card`);
    if(first){first.classList.add('chosen');if(first.dataset.img)preview.src=first.dataset.img;}
    message.textContent = `Categoría seleccionada: ${button.dataset.category}.`;
  }));
  $$('.model-card').forEach(button => button.addEventListener('click', () => {
    $$('.model-card').forEach(item => item.classList.remove('chosen'));
    button.classList.add('chosen');
    const name = button.dataset.model || button.innerText.trim();
    $('#previewNote').textContent = `Vista frontal · ${name}`;
    if(button.dataset.img) preview.src=button.dataset.img;
    message.textContent = `Modelo base: ${name}. Puedes seguir personalizándolo.`;
  }));
  $$('.option-row button').forEach(button => button.addEventListener('click', () => selectOne(`.${button.closest('.option-row').classList[1]} button`, button)));
  $$('.fabric-row button').forEach(button => button.addEventListener('click', () => selectOne('.fabric-row button', button)));
  $$('.swatch').forEach(button => button.addEventListener('click', () => {
    selectOne('.swatch', button);
    const color = getComputedStyle(button).backgroundColor;
    preview.style.filter = `sepia(.12) saturate(1.1)`;
    preview.style.backgroundColor = color;
    message.textContent = `Color seleccionado: ${button.getAttribute('aria-label')}. La vista es una maqueta visual.`;
  }));
  $$('.pattern').forEach(button => button.addEventListener('click', () => selectOne('.pattern', button)));
  $$('.view-switch button').forEach(button => button.addEventListener('click', () => {
    selectOne('.view-switch button', button);
    stage.classList.toggle('view-3d', button.dataset.view === '3D');
    message.textContent = button.dataset.view === '3D' ? 'Vista 3D demostrativa: el modelo aún no es un objeto 3D real.' : 'Vista 2D seleccionada.';
  }));
  function setAngle(angle) {
    $$('[data-angle]').forEach(button => button.classList.toggle('active', button.dataset.angle === angle));
    $('#previewNote').textContent = `Vista ${angle.toLowerCase()} · modelo seleccionado`;
    // Las vistas posteriores quedan preparadas para conectar imágenes/modelos reales.
    preview.style.transform = angle === 'Lateral' ? 'translate(-50%,-50%) scaleX(.72)' : 'translate(-50%,-50%)';
    if (angle === 'Espalda') preview.style.filter = 'brightness(.88)';
    else preview.style.filter = '';
  }
  $$('[data-angle]').forEach(button => button.addEventListener('click', () => setAngle(button.dataset.angle)));
  $$('.tool-rail button').forEach(button => button.addEventListener('click', () => {
    selectOne('.tool-rail button', button);
    if (button.dataset.tool === 'Restablecer') { zoom = 1; preview.style.transform = 'translate(-50%,-50%)'; $('#zoomLabel').textContent = '100%　⌄'; }
    if (button.dataset.tool === 'Zoom') { zoom = Math.min(1.3, zoom + .1); applyZoom(); }
    message.textContent = `Herramienta seleccionada: ${button.dataset.tool}.`;
  }));
  function applyZoom() { preview.style.transform = `translate(-50%,-50%) scale(${zoom})`; $('#zoomLabel').textContent = `${Math.round(zoom * 100)}%　⌄`; }
  $('#zoomIn').addEventListener('click', () => { zoom = Math.min(1.5, zoom + .1); applyZoom(); });
  $('#zoomOut').addEventListener('click', () => { zoom = Math.max(.6, zoom - .1); applyZoom(); });
  $('#fullscreen').addEventListener('click', () => {
    if (stage.requestFullscreen) stage.requestFullscreen().catch(() => {});
  });
  $$('input[type="range"]').forEach(input => input.addEventListener('input', () => {
    input.nextElementSibling.value = `${input.value}%`;
  }));
  $$('.step-tabs button').forEach(button => button.addEventListener('click', () => {
    selectOne('.step-tabs button', button);
    const stepNames = {1:'Diseño',2:'Colores y telas',3:'Detalles',4:'Guardar'};
    message.textContent = `Sección ${button.dataset.step}: ${stepNames[button.dataset.step]}. La navegación está preparada como maqueta.`;
  }));
  $('#editButton').addEventListener('click', () => { $('.options-panel').scrollIntoView({behavior:'smooth', block:'start'}); message.textContent = 'Selecciona las opciones de diseño en el panel derecho.'; });
  async function guardar(modo) {
    const selected = (sel) => $(sel)?.textContent.trim() || '';
    const form = new FormData();
    form.append('csrf', $('#designCsrf').value);
    form.append('modo', modo);
    form.append('modelo', $('.model-card.chosen')?.dataset.model || '');
    form.append('prenda_id', $('.model-card.chosen')?.dataset.prendaId || '');
    form.append('talla', $('#designSize').value);
    form.append('color', $('.swatch.active')?.getAttribute('aria-label') || 'Negro');
    form.append('tela', selected('.fabric-row button.active').replace(/^[^A-Za-zÁ-ú]*/, ''));
    form.append('escote', selected('.neckline button.active').replace(/^[^A-Za-zÁ-ú]*/, ''));
    form.append('manga', selected('.sleeve button.active').replace(/^[^A-Za-zÁ-ú]*/, ''));
    form.append('largo', selected('.length button.active').replace(/^[^A-Za-zÁ-ú]*/, ''));
    form.append('texto_personalizado', $('#designText').value.trim());
    form.append('ubicacion_texto', $('#designTextPosition').value);
    form.append('descripcion', $('#designText').value.trim() || 'Personalización básica creada en DiseñaTuPrenda.');
    if ($('#designReference').files[0]) form.append('referencia', $('#designReference').files[0]);
    message.textContent = 'Guardando...';
    try {
      const response = await fetch('index.php?r=guardar_diseno', {method:'POST', body:form, credentials:'same-origin'});
      const data = await response.json();
      message.textContent = data.mensaje || 'No se pudo completar la operación.';
      if (data.ok) message.style.color = '#66e3b3'; else message.style.color = '#ff9caa';
    } catch (error) { message.textContent = 'Error de conexión. Comprueba MySQL y vuelve a intentarlo.'; message.style.color='#ff9caa'; }
  }
  $('#designText').addEventListener('input', () => { $('#previewCustomText').textContent = $('#designText').value; });
  const firstPhoto=$('.model-card.chosen');if(firstPhoto?.dataset.img)preview.src=firstPhoto.dataset.img;
  $('#saveDesign').addEventListener('click', () => guardar('diseno'));
  $('#addCart').textContent = '✉  Enviar solicitud';
  $('#addCart').addEventListener('click', () => guardar('solicitud'));
})();
