(() => {
  'use strict';
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const $ = (selector, root = document) => root.querySelector(selector);
  const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  const modal = $('#editModal');
  const requestModal = $('#requestModal');
  const tableStates = [];

  $$('.management[data-module]').forEach(section => {
    const rows = $$('tbody tr', section);
    const search = $('[data-search]', section);
    const filter = $('[data-filter]', section);
    const count = $('[data-count]', section);
    const pages = $('[data-pages]', section);
    const state = {section, rows, search, filter, page:1};
    tableStates.push(state);
    const render = () => {
      const query = normalize(search.value);
      const value = normalize(filter.value);
      const matches = rows.filter(row => normalize(row.dataset.searchable).includes(query) && (!value || normalize(row.dataset.filterValue) === value));
      const pageCount = Math.max(1, Math.ceil(matches.length / 5));
      state.page = Math.min(state.page, pageCount);
      rows.forEach(row => { row.hidden = true; });
      matches.slice((state.page - 1) * 5, state.page * 5).forEach(row => { row.hidden = false; });
      count.textContent = `Mostrando ${matches.length ? (state.page - 1) * 5 + 1 : 0} - ${Math.min(state.page * 5, matches.length)} de ${matches.length}`;
      pages.replaceChildren();
      const makePage = (label, number, disabled = false) => {
        const button = document.createElement('button'); button.type = 'button'; button.textContent = label;
        button.disabled = disabled;button.classList.toggle('active', number === state.page && label !== '‹' && label !== '›');
        button.addEventListener('click', () => {state.page = number;render();}); pages.appendChild(button);
      };
      makePage('‹', Math.max(1,state.page-1),state.page===1);
      let first = Math.max(1, state.page-3);
      let last = Math.min(pageCount, first+6);
      first = Math.max(1,last-6);
      if(first>1)makePage('1',1);
      if(first>2){const dots=document.createElement('span');dots.textContent='…';dots.className='pages-ellipsis';pages.append(dots);}
      for(let n=first;n<=last;n++) makePage(String(n),n);
      if(last<pageCount-1){const dots=document.createElement('span');dots.textContent='…';dots.className='pages-ellipsis';pages.append(dots);}
      if(last<pageCount)makePage(String(pageCount),pageCount);
      makePage('›', Math.min(pageCount,state.page+1),state.page===pageCount);
      if(!rows.length && !$('.empty-row',section)){
        const tr = document.createElement('tr');tr.className='empty-row';
        const td=document.createElement('td');td.colSpan=$$('thead th',section).length;td.textContent='Sin registros. Usa el botón de nuevo registro o importa datos de ejemplo.';tr.append(td);$('tbody',section).append(tr);
      }
    };
    state.render = render;
    search.addEventListener('input',() => {state.page=1;render();});
    filter.addEventListener('change',() => {state.page=1;render();});
    render();
  });
  const globalSearch=$('#globalSearch');
  globalSearch?.addEventListener('input', () => {tableStates.forEach(s => {s.search.value=globalSearch.value;s.page=1;s.render();});});

  function openEditor(module, record = {}) {
    const isUser = module === 'usuarios';const isGarment = module === 'prendas';const isNew=!record.id;
    $('#formModule').value=module;$('#formId').value=record.id||'';
    $('#formName').value=record.nombre||'';$('#formDescription').value=record.descripcion||'';
    $('#formEmail').value=record.correo||'';$('#formPassword').value='';
    $('#formRole').value=String(record.rol_id || (record.rol==='administrador'?'1':'2'));
    $('#categoryDescription').hidden=isUser;
    $('#garmentCategory').hidden=!isGarment;
    $('#userFields').hidden=!isUser;
    $('#formCategory').value=String(record.categoria_id||$('#formCategory').options[0]?.value||'');
    const status=$('#formStatus');status.replaceChildren();
    (isUser?['Activo','Bloqueado']:['Activo','Inactivo']).forEach(s=>{const opt=document.createElement('option');opt.textContent=s;opt.value=s;status.append(opt);});
    status.value=record.estado||'Activo';
    $('#formEmail').required=isUser;$('#formPassword').required=isUser&&isNew;$('#formImage').value='';
    $('#imageHelp').textContent=record.imagen?'Imagen actual registrada. Puedes subir otra para reemplazarla.':'Imagen opcional. JPG, PNG o WebP, máximo 3 MB.';
    $('#modalTitle').textContent=(isNew?'Nuevo registro: ':'Editar: ')+({usuarios:'usuarios',categorias:'categorías',prendas:'prendas'}[module]);
    modal.hidden=false;$('#formName').focus();
  }
  $$('[data-new]').forEach(button=>button.addEventListener('click',()=>openEditor(button.dataset.new)));
  $$('[data-edit]').forEach(button=>button.addEventListener('click',()=>{
    try{openEditor(button.dataset.edit,JSON.parse(button.dataset.record));}
    catch(error){alert('No se pudo cargar la información del registro.');}
  }));
  $$('[data-state]').forEach(button=>button.addEventListener('click',()=>{
    if(!confirm(`¿Confirmas el cambio de estado a "${button.dataset.next}"?`))return;
    const form=$('#stateForm');
    form.elements.namedItem('modulo').value=button.dataset.state;
    form.elements.namedItem('id').value=button.dataset.id;
    form.elements.namedItem('estado').value=button.dataset.next;
    form.submit();
  }));
  $$('[data-request]').forEach(button=>button.addEventListener('click',()=>{
    try{
      const item=JSON.parse(button.dataset.request);
      $('#requestId').value=item.id;$('#requestStatus').value=item.estado;
      const details=$('#requestDetails');details.replaceChildren();
      [['Número',item.id],['Usuario',item.usuario],['Diseño',item.diseno],['Descripción',item.descripcion||'Sin descripción'],['Fecha',item.creado_en]].forEach(([key,value])=>{
        const line=document.createElement('div');const label=document.createElement('strong');label.textContent=key+': ';
        line.append(label,document.createTextNode(String(value)));details.append(line);
      });requestModal.hidden=false;
    }catch(error){alert('No se pudo abrir la solicitud.');}
  }));
  const newRequestModal=$('#newRequestModal');
  $('#newRequestButton')?.addEventListener('click',()=>{newRequestModal.hidden=false;});
  const noticeButton=$('#notificationButton'),noticeMenu=$('#noticeMenu');
  noticeButton?.addEventListener('click',()=>{noticeMenu.hidden=!noticeMenu.hidden;noticeButton.setAttribute('aria-expanded',String(!noticeMenu.hidden));});
  noticeMenu?.querySelector('a')?.addEventListener('click',()=>{noticeMenu.hidden=true;noticeButton.setAttribute('aria-expanded','false');});
  $$('[data-close]').forEach(button=>button.addEventListener('click',()=>{modal.hidden=true;requestModal.hidden=true;newRequestModal.hidden=true;}));
  [modal,requestModal,newRequestModal].forEach(m=>m.addEventListener('click',event=>{if(event.target===m)m.hidden=true;}));
  document.addEventListener('keydown',event=>{if(event.key==='Escape'){modal.hidden=true;requestModal.hidden=true;newRequestModal.hidden=true;noticeMenu.hidden=true;noticeButton?.setAttribute('aria-expanded','false');}});
  // La navegación por módulos es una ruta PHP real. No se desplaza a otra sección en la misma pantalla.
  $$('[data-settings]').forEach(button=>button.addEventListener('click',()=>{
    const selected=button.dataset.settings;
    $$('[data-settings]').forEach(b=>b.classList.toggle('selected',b===button));
    $$('[data-settings-panel]').forEach(panel=>panel.hidden=panel.dataset.settingsPanel!==selected);
  }));
})();
