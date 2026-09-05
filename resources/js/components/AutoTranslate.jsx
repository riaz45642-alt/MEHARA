import { useEffect } from 'react'
import { useApp } from '../context/AppContext'
import { useRouter } from '../router/Router'
import { pairs } from '../i18n/translations'

const hasArabic=value=>/[\u0600-\u06ff]/.test(value)
const normalized=pairs.map(([first,second])=>hasArabic(first)?[first,second]:[second,first])
const arToEn=new Map(normalized),enToAr=new Map(normalized.map(([ar,en])=>[en,ar]))

export default function AutoTranslate(){
  const {language}=useApp(); const {path}=useRouter()
  useEffect(()=>{
    const frame=requestAnimationFrame(()=>{
      const map=language==='en'?arToEn:enToAr,root=document.getElementById('app'); if(!root)return
      const walker=document.createTreeWalker(root,NodeFilter.SHOW_TEXT); let node
      while((node=walker.nextNode())){
        if(['SCRIPT','STYLE'].includes(node.parentElement?.tagName))continue
        const raw=node.nodeValue,value=raw.trim(); if(!map.has(value))continue
        const lead=raw.match(/^\s*/)?.[0]||'',trail=raw.match(/\s*$/)?.[0]||''; node.nodeValue=lead+map.get(value)+trail
      }
      root.querySelectorAll('input[placeholder],textarea[placeholder],[title],[aria-label]').forEach(el=>{
        for(const attr of ['placeholder','title','aria-label']){const raw=el.getAttribute(attr);if(raw&&map.has(raw))el.setAttribute(attr,map.get(raw))}
      })
    })
    return()=>cancelAnimationFrame(frame)
  },[language,path])
  return null
}
