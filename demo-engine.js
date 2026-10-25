(function(root){
 'use strict';
 function remaining(concert,orders){return Math.max(0,concert.stok-orders.filter(o=>o.concertId===concert.id).reduce((sum,o)=>sum+o.jumlah,0));}
 function validateOrders(value,concerts){
  if(!value || value.version!==1 || !Array.isArray(value.orders))throw new Error('Data demo tersimpan tidak terbaca. Gunakan Reset demo untuk mulai kembali.');
  const codes=new Set();
  for(const o of value.orders){
   const c=concerts.find(c=>c.id===o.concertId);
   if(!c || typeof o.kode!=='string' || !/^DEMO-[A-F0-9]{16}$/.test(o.kode) || codes.has(o.kode) || typeof o.nama!=='string' || o.nama.length<2 || o.nama.length>100 || typeof o.email!=='string' || o.email.length>150 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(o.email) || !Number.isInteger(o.jumlah) || o.jumlah<1 || o.jumlah>5 || o.total!==c.harga*o.jumlah || !Number.isFinite(Date.parse(o.dibuat)))throw new Error('Data demo tersimpan tidak valid. Gunakan Reset demo untuk mulai kembali.');
   codes.add(o.kode);
  }
  for(const c of concerts){if(value.orders.filter(o=>o.concertId===c.id).reduce((n,o)=>n+o.jumlah,0)>c.stok)throw new Error('Stok demo tidak valid. Gunakan Reset demo.');}
  return value.orders;
 }
 function createOrder(concerts,orders,input,kode,now){
  const concert=concerts.find(c=>c.id===input.concertId);
  const nama=String(input.nama||'').trim(),email=String(input.email||'').trim(),jumlah=Number(input.jumlah);
  if(!concert)throw new Error('Pilih konser terlebih dahulu.');
  if(nama.length<2 || nama.length>100)throw new Error('Nama harus berisi 2–100 karakter.');
  if(email.length>150 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))throw new Error('Masukkan alamat email yang valid.');
  if(!Number.isInteger(jumlah)||jumlah<1||jumlah>5)throw new Error('Jumlah tiket harus 1–5.');
  if(jumlah>remaining(concert,orders))throw new Error('Stok demo tidak cukup. Kurangi jumlah tiket.');
  if(orders.some(o=>o.kode===kode))throw new Error('Kode booking sudah digunakan. Coba lagi.');
  return {kode,concertId:concert.id,nama,email,jumlah,total:concert.harga*jumlah,dibuat:now};
 }
 root.NadaEngine={remaining,validateOrders,createOrder};
})(globalThis);
