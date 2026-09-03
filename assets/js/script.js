const header=document.querySelector('#site-header'),toggle=document.querySelector('#menu-toggle'),menu=document.querySelector('#mobile-nav');
const setHeader=()=>header?.classList.toggle('scrolled',window.scrollY>24);setHeader();window.addEventListener('scroll',setHeader,{passive:true});
toggle?.addEventListener('click',()=>{const open=document.body.classList.toggle('menu-open');toggle.setAttribute('aria-expanded',open?'true':'false')});
menu?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>document.body.classList.remove('menu-open')));
const syncMobileTrigger=()=>{if(!toggle)return;const mobile=window.innerWidth<=900;toggle.style.setProperty('display',mobile?'grid':'none','important');if(mobile){toggle.textContent=document.body.classList.contains('menu-open')?'×':'☰';toggle.style.placeItems='center';}};syncMobileTrigger();window.addEventListener('resize',syncMobileTrigger);toggle?.addEventListener('click',()=>{toggle.textContent=document.body.classList.contains('menu-open')?'×':'☰';});
const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('revealed')}),{threshold:.12});
document.querySelectorAll('section,.card,.destination-card').forEach(el=>{el.classList.add('reveal');observer.observe(el)});

const chatToggle=document.querySelector('#ai-chat-toggle'),chatPanel=document.querySelector('#ai-chat-panel'),chatClose=document.querySelector('#ai-chat-close'),chatForm=document.querySelector('#ai-chat-form'),chatInput=document.querySelector('#ai-chat-input'),chatMessages=document.querySelector('#ai-chat-messages');
const chatHistory=[];
const setChatOpen=open=>{if(!chatPanel)return;chatPanel.classList.toggle('open',open);chatPanel.setAttribute('aria-hidden',open?'false':'true');chatToggle?.setAttribute('aria-expanded',open?'true':'false');if(open)setTimeout(()=>chatInput?.focus(),150)};
chatToggle?.addEventListener('click',()=>setChatOpen(!chatPanel?.classList.contains('open')));
chatClose?.addEventListener('click',()=>setChatOpen(false));
window.openGeminiChat=()=>setChatOpen(true);
const renderAssistantText=(el,text)=>{
  const escapeHtml=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
  const inline=value=>value.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>');
  const lines=escapeHtml(text).split(/\r?\n/);let html='',inList=false;
  lines.forEach(line=>{const item=line.match(/^\s*[-•]\s+(.+)/);if(item){if(!inList){html+='<ul>';inList=true}html+=`<li>${inline(item[1])}</li>`;return}if(inList){html+='</ul>';inList=false}html+=line.trim()?`<p>${inline(line)}</p>`:'<br>'});
  if(inList)html+='</ul>';el.innerHTML=html;
};
const addChatMessage=(text,role,extra='')=>{const el=document.createElement('div');el.className=`ai-message ${role} ${extra}`.trim();if(role==='assistant'&&extra!=='loading')renderAssistantText(el,text);else el.textContent=text;chatMessages?.appendChild(el);if(chatMessages)chatMessages.scrollTop=chatMessages.scrollHeight;return el};
chatInput?.addEventListener('input',()=>{chatInput.style.height='auto';chatInput.style.height=Math.min(chatInput.scrollHeight,110)+'px'});
chatInput?.addEventListener('keydown',event=>{if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();chatForm?.requestSubmit()}});
chatForm?.addEventListener('submit',async event=>{
  event.preventDefault();const message=chatInput.value.trim();if(!message)return;
  const priorHistory=chatHistory.slice(-10);addChatMessage(message,'user');chatHistory.push({role:'user',text:message});chatInput.value='';chatInput.style.height='auto';
  const submit=chatForm.querySelector('button[type="submit"]');submit.disabled=true;chatInput.disabled=true;const waiting=addChatMessage('Thinking…','assistant','loading');
  try{const response=await fetch('chat-api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message,history:priorHistory})});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.error||'Something went wrong.');waiting.remove();addChatMessage(data.reply,'assistant');chatHistory.push({role:'assistant',text:data.reply})}
  catch(error){waiting.remove();addChatMessage(error.message||'I could not answer right now. Please try again.','assistant','error')}
  finally{submit.disabled=false;chatInput.disabled=false;chatInput.focus()}
});
