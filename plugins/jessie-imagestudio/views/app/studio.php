<?php /** Image Studio — User App */ ?>
<div class="saas-header"><h1>🖼️ Image Studio</h1></div>
<div class="saas-content">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
  <div class="saas-card" style="margin-bottom:16px">
    <h3 style="margin-bottom:12px">Upload Image</h3>
    <div id="is-dropzone" style="border:2px dashed #334155;border-radius:12px;padding:40px;text-align:center;cursor:pointer;transition:.15s" ondragover="event.preventDefault();this.style.borderColor='#8b5cf6'" ondragleave="this.style.borderColor='#334155'" ondrop="isDrop(event)" onclick="document.getElementById('is-file').click()">
      <div style="font-size:36px;margin-bottom:8px">📁</div>
      <div style="color:#94a3b8">Drop image here or click to upload</div>
      <div style="color:#64748b;font-size:12px;margin-top:4px">JPG, PNG, WebP, GIF · Max 25MB</div>
    </div>
    <input type="file" id="is-file" accept="image/*" style="display:none" onchange="isUpload(this.files[0])">
  </div>
  <div class="saas-card" id="is-tools" style="display:none">
    <h3 style="margin-bottom:12px">Tools</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
      <button class="saas-btn saas-btn-ghost" onclick="isTool('remove-bg')">🪄 Remove BG (1cr)</button>
      <button class="saas-btn saas-btn-ghost" onclick="isTool('alt-text')">📝 Alt Text (1cr)</button>
      <button class="saas-btn saas-btn-ghost" onclick="isTool('enhance')">✨ Enhance (2cr)</button>
      <button class="saas-btn saas-btn-ghost" onclick="isGenerate()">🎨 Generate (3cr)</button>
    </div>
    <div style="margin-top:12px">
      <label class="saas-label">Resize Preset</label>
      <select class="saas-input" id="is-preset"><option value="">Custom</option><option value="ig-square">Instagram Square 1080×1080</option><option value="ig-story">Instagram Story 1080×1920</option><option value="fb-cover">Facebook Cover 820×312</option><option value="twitter">Twitter/X 1200×675</option><option value="linkedin">LinkedIn 1200×627</option><option value="yt-thumb">YouTube Thumb 1280×720</option><option value="pinterest">Pinterest 1000×1500</option><option value="og-image">OG Image 1200×630</option></select>
      <button class="saas-btn saas-btn-ghost" style="margin-top:8px;width:100%" onclick="isResize()">📐 Resize (free)</button>
    </div>
  </div>
  <div class="saas-card" style="margin-top:16px">
    <h3 style="margin-bottom:12px">Generate from Text</h3>
    <input class="saas-input" id="is-prompt" placeholder="A professional product photo of...">
    <select class="saas-input" id="is-style" style="margin-top:8px"><option value="photo">Photo</option><option value="illustration">Illustration</option><option value="3d">3D Render</option><option value="flat">Flat Design</option></select>
    <button class="saas-btn saas-btn-primary" style="margin-top:8px;width:100%" onclick="isGenerate()">🎨 Generate Image (3 credits)</button>
  </div>
</div>
<div>
  <div class="saas-card" id="is-preview" style="min-height:300px;text-align:center">
    <div style="color:#64748b;padding:60px 0">No image selected</div>
  </div>
  <div class="saas-card" style="margin-top:16px" id="is-result-box" style="display:none">
    <h3 style="margin-bottom:8px">Result</h3>
    <div id="is-result" style="font-size:13px;color:#94a3b8"></div>
  </div>
  <div class="saas-card" style="margin-top:16px">
    <h3 style="margin-bottom:8px">Recent Images</h3>
    <div id="is-gallery" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px"></div>
  </div>
</div>
</div>
</div>
<script>
const API='/api/imagestudio';const hdr={'X-API-Key':'<?= htmlspecialchars($user['api_key']??'') ?>'};let currentImageId=null;
function isDrop(e){e.preventDefault();e.currentTarget.style.borderColor='#334155';if(e.dataTransfer.files.length)isUpload(e.dataTransfer.files[0]);}
async function isUpload(file){if(!file)return;const fd=new FormData();fd.append('image',file);
const r=await fetch(API+'/upload',{method:'POST',headers:{'X-API-Key':hdr['X-API-Key']},body:fd});
const d=await r.json();if(d.success){currentImageId=d.image.id;document.getElementById('is-preview').innerHTML=`<img src="${d.image.file_url}" style="max-width:100%;border-radius:8px">`;document.getElementById('is-tools').style.display='block';loadGallery();}else alert(d.error);}
async function isTool(action){if(!currentImageId)return alert('Upload an image first');
document.getElementById('is-result').textContent='Processing...';document.getElementById('is-result-box').style.display='block';
const body={image_id:currentImageId};if(action==='enhance')body.prompt='enhance quality, sharpen, improve lighting';
const r=await fetch(API+'/'+action,{method:'POST',headers:{...hdr,'Content-Type':'application/json'},body:JSON.stringify(body)});
const d=await r.json();
if(d.success){
  if(d.image)document.getElementById('is-result').innerHTML=`<img src="${d.image.file_url}" style="max-width:100%;border-radius:8px;margin-top:8px">`;
  if(d.alt)document.getElementById('is-result').textContent='Alt text: '+d.alt;
  loadGallery();
}else document.getElementById('is-result').textContent=d.error;}
async function isGenerate(){const prompt=document.getElementById('is-prompt').value;if(!prompt)return alert('Enter a prompt');
document.getElementById('is-result').textContent='Generating...';document.getElementById('is-result-box').style.display='block';
const r=await fetch(API+'/generate',{method:'POST',headers:{...hdr,'Content-Type':'application/json'},body:JSON.stringify({prompt,style:document.getElementById('is-style').value})});
const d=await r.json();if(d.success){document.getElementById('is-result').innerHTML=`<img src="${d.image.file_url}" style="max-width:100%;border-radius:8px">`;loadGallery();}else document.getElementById('is-result').textContent=d.error;}
async function isResize(){if(!currentImageId)return alert('Upload an image first');const preset=document.getElementById('is-preset').value;if(!preset)return alert('Select a preset');
const r=await fetch(API+'/resize-preset',{method:'POST',headers:{...hdr,'Content-Type':'application/json'},body:JSON.stringify({image_id:currentImageId,preset})});
const d=await r.json();if(d.success)document.getElementById('is-result').innerHTML=`<div>Resized to ${d.result.width}×${d.result.height}</div>`;else document.getElementById('is-result').textContent=d.error;}
async function loadGallery(){const r=await fetch(API+'/images?limit=6',{headers:hdr});const d=await r.json();
if(d.success){let h='';d.images.forEach(i=>{h+=`<div style="cursor:pointer" onclick="currentImageId=${i.id};document.getElementById('is-preview').innerHTML='<img src=\\'${i.file_url}\\' style=\\'max-width:100%;border-radius:8px\\'>';document.getElementById('is-tools').style.display='block'"><img src="${i.file_url}" style="width:100%;height:80px;object-fit:cover;border-radius:6px" loading="lazy"></div>`;});document.getElementById('is-gallery').innerHTML=h||'<div style="color:#64748b;grid-column:1/-1">No images yet</div>';}}
loadGallery();
</script>
