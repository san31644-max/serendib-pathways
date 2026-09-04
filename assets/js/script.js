const header=document.querySelector('#site-header'),toggle=document.querySelector('#menu-toggle'),menu=document.querySelector('#mobile-nav');
const setHeader=()=>header?.classList.toggle('scrolled',window.scrollY>24);setHeader();window.addEventListener('scroll',setHeader,{passive:true});
toggle?.addEventListener('click',()=>{const open=document.body.classList.toggle('menu-open');toggle.setAttribute('aria-expanded',open?'true':'false')});
menu?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>document.body.classList.remove('menu-open')));
const syncMobileTrigger=()=>{if(!toggle)return;const mobile=window.innerWidth<=900;toggle.style.setProperty('display',mobile?'grid':'none','important');if(mobile){toggle.textContent=document.body.classList.contains('menu-open')?'×':'☰';toggle.style.placeItems='center';}};syncMobileTrigger();window.addEventListener('resize',syncMobileTrigger);toggle?.addEventListener('click',()=>{toggle.textContent=document.body.classList.contains('menu-open')?'×':'☰';});
const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('revealed')}),{threshold:.12});
document.querySelectorAll('section,.card,.destination-card').forEach(el=>{el.classList.add('reveal');observer.observe(el)});

const heroVideo=document.querySelector('#hero-video'),heroSoundToggle=document.querySelector('#hero-sound-toggle');
const syncHeroSound=()=>{if(!heroVideo||!heroSoundToggle)return;const audible=!heroVideo.muted&&heroVideo.volume>0;heroSoundToggle.setAttribute('aria-pressed',audible?'true':'false');heroSoundToggle.setAttribute('aria-label',audible?'Mute video sound':'Play video sound');heroSoundToggle.querySelector('i').className=`fa-solid ${audible?'fa-volume-high':'fa-volume-xmark'}`;heroSoundToggle.querySelector('span').textContent=audible?'Sound on':'Play sound'};
if(heroVideo){heroVideo.volume=.8;heroVideo.muted=false;heroVideo.play().then(syncHeroSound).catch(()=>{heroVideo.muted=true;heroVideo.play().catch(()=>{});syncHeroSound()})}
heroSoundToggle?.addEventListener('click',async()=>{if(!heroVideo)return;heroVideo.muted=!heroVideo.muted;if(!heroVideo.muted)heroVideo.volume=.8;try{await heroVideo.play()}catch{}syncHeroSound()});

const chatToggle=document.querySelector('#ai-chat-toggle'),chatPanel=document.querySelector('#ai-chat-panel'),chatClose=document.querySelector('#ai-chat-close'),chatForm=document.querySelector('#ai-chat-form'),chatInput=document.querySelector('#ai-chat-input'),chatMessages=document.querySelector('#ai-chat-messages');
const chatHistory=[];
let humanMode=false,humanToken=localStorage.getItem('serendibHumanChat')||'',humanLastMessage=0,humanPollTimer=null;
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
const humanStart=document.querySelector('#human-chat-start'),humanBanner=document.querySelector('#human-chat-banner'),humanState=document.querySelector('#human-chat-state'),humanExit=document.querySelector('#human-chat-exit'),assistantName=document.querySelector('#chat-assistant-name'),assistantStatus=document.querySelector('#chat-assistant-status');
const humanCall=async data=>{const response=await fetch('human-chat-api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});const result=await response.json().catch(()=>({}));if(!response.ok)throw new Error(result.error||'Human chat is unavailable.');return result};
const enterHumanMode=()=>{humanMode=true;humanBanner.hidden=false;assistantName.textContent='Travel team';assistantStatus.textContent='Human conversation';chatInput.placeholder='Message our travel specialist…';if(humanPollTimer)clearInterval(humanPollTimer);humanPollTimer=setInterval(pollHumanChat,3000);pollHumanChat()};
const leaveHumanMode=()=>{humanMode=false;humanBanner.hidden=true;assistantName.textContent='Serendib Pathways Assistant';assistantStatus.textContent='Powered by Gemini';chatInput.placeholder='Ask me anything…';if(humanPollTimer){clearInterval(humanPollTimer);humanPollTimer=null}};
const pollHumanChat=async()=>{if(!humanMode||!humanToken)return;try{const data=await humanCall({action:'poll',token:humanToken,after:humanLastMessage});data.messages.forEach(message=>{humanLastMessage=Math.max(humanLastMessage,+message.id);if(message.sender==='visitor')return;addChatMessage(message.message,'assistant',message.sender==='system'?'human-system':'human-agent');const last=chatMessages.lastElementChild;if(last&&message.sender==='agent'){const label=document.createElement('strong');label.className='human-sender';label.textContent=message.sender_name;last.prepend(label)}});humanState.textContent=data.status==='active'?`${data.agent_name||'A travel specialist'} is here`:(data.status==='closed'?'Conversation closed':'Waiting for a travel specialist…')}catch(error){humanState.textContent='Reconnecting to the travel team…'}};
humanStart?.addEventListener('click',async()=>{try{if(!humanToken){const visitorName=prompt('What name should our travel specialist call you?','Guest')||'Guest';const data=await humanCall({action:'start',name:visitorName});humanToken=data.token;localStorage.setItem('serendibHumanChat',humanToken);humanLastMessage=0;chatMessages.innerHTML=''}enterHumanMode()}catch(error){addChatMessage(error.message,'assistant','error')}});
humanExit?.addEventListener('click',leaveHumanMode);
chatInput?.addEventListener('input',()=>{chatInput.style.height='auto';chatInput.style.height=Math.min(chatInput.scrollHeight,110)+'px'});
chatInput?.addEventListener('keydown',event=>{if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();chatForm?.requestSubmit()}});
chatForm?.addEventListener('submit',async event=>{
  event.preventDefault();const message=chatInput.value.trim();if(!message)return;
  if(humanMode){addChatMessage(message,'user');chatInput.value='';chatInput.style.height='auto';try{await humanCall({action:'send',token:humanToken,message})}catch(error){addChatMessage(error.message,'assistant','error')}return}
  const priorHistory=chatHistory.slice(-10);addChatMessage(message,'user');chatHistory.push({role:'user',text:message});chatInput.value='';chatInput.style.height='auto';
  const submit=chatForm.querySelector('button[type="submit"]');submit.disabled=true;chatInput.disabled=true;const waiting=addChatMessage('Thinking…','assistant','loading');
  try{const response=await fetch('chat-api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message,history:priorHistory})});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.error||'Something went wrong.');waiting.remove();addChatMessage(data.reply,'assistant');chatHistory.push({role:'assistant',text:data.reply})}
  catch(error){waiting.remove();addChatMessage(error.message||'I could not answer right now. Please try again.','assistant','error')}
  finally{submit.disabled=false;chatInput.disabled=false;chatInput.focus()}
});
