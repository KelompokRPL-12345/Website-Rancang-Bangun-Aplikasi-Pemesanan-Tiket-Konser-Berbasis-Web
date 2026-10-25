'use strict';
const concerts=window.NADA_CONCERTS;
const engine=window.NadaEngine;
const key='moonvenue-demo-v1:'+location.pathname.replace(/index\.html$/,'');
let memory=[],persistent=true,corrupt=false,busy=false;
const $=id=>document.getElementById(id);
const money=value=>new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(value);
function element(tag,text,cls){const node=document.createElement(tag);if(text!==undefined)node.textContent=text;if(cls)node.className=cls;return node;}
function notice(message){$('notice').textContent=message;$('notice').hidden=!message;}
function unavailableStorage(){persistent=false;$('storage-note').textContent='Penyimpanan browser tidak tersedia. Demo tetap bisa dicoba, tetapi pesanan akan hilang setelah halaman ditutup atau dimuat ulang.';}
function load(){
 if(!persistent)return memory;
 let raw;
 try{raw=localStorage.getItem(key);}catch{unavailableStorage();return memory;}
 try{const orders=raw?engine.validateOrders(JSON.parse(raw),concerts):[];corrupt=false;memory=orders;return orders;}
 catch(error){corrupt=true;notice(error.message);return [];}
}
function save(orders){if(persistent){try{localStorage.setItem(key,JSON.stringify({version:1,orders}));}catch{unavailableStorage();}}memory=orders;}
let orders=load();
function updateTotal(){const c=concerts.find(c=>c.id===$('konser_id').value);const count=Number($('jumlah').value);$('jumlah').max=c?Math.max(1,Math.min(5,engine.remaining(c,orders))):5;$('total').textContent=money(c&&Number.isFinite(count)&&count>0?c.harga*count:0);}
function renderCatalog(){
 const selected=$('konser_id').value;$('concert-grid').replaceChildren();$('konser_id').replaceChildren(new Option('Pilih konser yang ingin kamu datangi',''));
 for(const c of concerts){
  const stock=engine.remaining(c,orders),card=element('article',undefined,'card');const poster=element('div',undefined,'concert-poster'),img=element('img');img.src=c.poster;img.alt='Poster '+c.nama;img.loading='lazy';poster.append(img);card.append(poster);
  const body=element('div',undefined,'card-body');body.append(element('h3',c.nama,'concert-name'),element('p',c.tanggal,'date'),element('p',c.lokasi,'location'));
  const price=element('div',undefined,'price'),amount=element('div');amount.append(element('small','Harga contoh per tiket'),element('strong',money(c.harga)));price.append(amount,element('span',stock+' tersisa (demo)'));body.append(price);
  const choose=element('button',stock?'Coba pesan tiket ↗':'Stok demo habis','choose');choose.type='button';choose.disabled=!stock||corrupt;
  choose.addEventListener('click',()=>{$('konser_id').value=c.id;$('jumlah').value=1;updateTotal();$('pesan').scrollIntoView({behavior:'smooth'});$('nama').focus({preventScroll:true});});body.append(choose);card.append(body);$('concert-grid').append(card);
  const option=new Option(c.nama+(stock?'':' — stok habis'),c.id);option.disabled=!stock;$('konser_id').append(option);
 }
 $('konser_id').value=selected;$('submit-order').disabled=corrupt;updateTotal();
}
function showBooking(order,focus=false){
 const c=concerts.find(c=>c.id===order.concertId);$('booking').hidden=false;$('booking-detail').replaceChildren();
 $('booking-detail').append(element('h3',c.nama),element('p',c.tanggal+' · '+c.lokasi),element('p','Pemesan: '+order.nama),element('p','Email contoh: '+order.email),element('p',order.jumlah+' tiket · '+money(order.total)));
 const code=element('p','Kode demo: ');code.append(element('code',order.kode));$('booking-detail').append(code,element('p','Dibuat: '+new Date(order.dibuat).toLocaleString('id-ID'),'muted'));
 if(focus){$('booking').scrollIntoView({behavior:'smooth'});$('booking').focus({preventScroll:true});}
}
function renderHistory(){
 $('history-list').replaceChildren();$('empty-history').hidden=orders.length>0;
 for(const order of [...orders].reverse()){
  const row=element('article',undefined,'history-row'),info=element('div'),c=concerts.find(c=>c.id===order.concertId);
  info.append(element('strong',c.nama),element('p',order.kode+' · '+order.jumlah+' tiket · '+money(order.total)));
  const view=element('button','Lihat bukti','reset');view.type='button';view.addEventListener('click',()=>showBooking(order,true));row.append(info,view);$('history-list').append(row);
 }
}
function render(){renderCatalog();renderHistory();}
$('konser_id').addEventListener('change',updateTotal);$('jumlah').addEventListener('input',updateTotal);
$('order-form').addEventListener('submit',async event=>{
 event.preventDefault();if(busy||!$('order-form').reportValidity())return;
 busy=true;$('submit-order').disabled=true;$('form-error').hidden=true;
 const input={concertId:$('konser_id').value,nama:$('nama').value,email:$('email').value,jumlah:$('jumlah').value};
 try{
  const book=()=>{
   orders=load();if(corrupt)throw new Error('Reset data demo yang tidak valid sebelum membuat pesanan.');
   const bytes=new Uint8Array(8);crypto.getRandomValues(bytes);const kode='DEMO-'+Array.from(bytes,b=>b.toString(16).padStart(2,'0')).join('').toUpperCase();
   const order=engine.createOrder(concerts,orders,input,kode,new Date().toISOString());
   orders=[...orders,order];save(orders);$('order-form').reset();render();showBooking(order,true);
  };
  if(navigator.locks)await navigator.locks.request(key,book);else book();
 }catch(error){$('form-error').textContent=error.message;$('form-error').hidden=false;renderCatalog();}
 finally{busy=false;$('submit-order').disabled=corrupt;}
});
$('print').addEventListener('click',()=>window.print());
$('reset').addEventListener('click',async()=>{
 if(busy||!confirm('Hapus semua pesanan demo di browser ini dan kembalikan stok awal?'))return;
 const reset=()=>{try{localStorage.removeItem(key);}catch{unavailableStorage();}memory=[];orders=[];corrupt=false;notice('Demo direset. Stok kembali ke jumlah awal.');$('booking').hidden=true;$('order-form').reset();$('form-error').hidden=true;render();};
 if(navigator.locks)await navigator.locks.request(key,reset);else reset();
});
window.addEventListener('storage',event=>{if(event.key===key){orders=load();render();if(orders.length)showBooking(orders[orders.length-1]);else $('booking').hidden=true;}});
$('year').textContent=new Date().getFullYear();render();if(orders.length)showBooking(orders[orders.length-1]);


document.getElementById("help").addEventListener("click",()=>document.getElementById("help-dialog").showModal());
