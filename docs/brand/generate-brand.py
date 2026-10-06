import sys, os
out = sys.argv[1]

THEMES = {
    'light': dict(bg='#ffffff', bg2='#f7f1e8', edge='#5a3510',
                  metal=[('0', '#6b3f12'), ('0.28', '#c98a3d'), ('0.5', '#f6d892'), ('0.72', '#b06f24'), ('1', '#5e3710')],
                  ink='#4a2a0b', glow=None),
    'dark': dict(bg='#050505', bg2='#16181c', edge='#0b0b0b',
                 metal=[('0', '#7d838c'), ('0.3', '#d9dde3'), ('0.5', '#ffffff'), ('0.7', '#a7adb6'), ('1', '#5f656e')],
                 ink='#1d2025', glow='#cfd6e0'),
}

def defs(t):
    stops = ''.join(f'<stop offset="{o}" stop-color="{c}"/>' for o, c in t['metal'])
    glow = ''
    if t['glow']:
        glow = ('<filter id="glow" x="-10%" y="-10%" width="120%" height="120%">'
                '<feGaussianBlur stdDeviation="5" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>')
    return (f'<defs><linearGradient id="metal" x1="0" y1="0" x2="1" y2="1">{stops}</linearGradient>'
            f'<linearGradient id="metalV" x1="0" y1="0" x2="0" y2="1">{stops}</linearGradient>'
            f'<radialGradient id="face" cx="50%" cy="42%" r="60%"><stop offset="0" stop-color="{t["bg2"]}"/><stop offset="1" stop-color="{t["bg"]}"/></radialGradient>'
            f'{glow}</defs>')

def sword(t, s=1.0):
    # Centred on x=256; ornate dagger: pommel, wrapped grip, curled crossguard, fullered blade.
    ink = t['ink']
    return f'''<g transform="translate(256 0) scale({s} 1) translate(-256 0)">
  <path d="M256 92 l11 14 -11 14 -11 -14z" fill="url(#metal)" stroke="{ink}" stroke-width="1.5"/>
  <circle cx="256" cy="128" r="9" fill="url(#metal)" stroke="{ink}" stroke-width="1.5"/>
  <rect x="248" y="136" width="16" height="44" rx="4" fill="url(#metalV)" stroke="{ink}" stroke-width="1.5"/>
  <path d="M248 146 h16 M248 156 h16 M248 166 h16" stroke="{ink}" stroke-width="2" opacity=".55"/>
  <path d="M256 178 C240 176 226 168 214 156 C208 150 200 152 202 160 C205 170 218 172 222 166
           C232 182 244 190 256 192 C268 190 280 182 290 166 C294 172 307 170 310 160 C312 152 304 150 298 156
           C286 168 272 176 256 178z" fill="url(#metal)" stroke="{ink}" stroke-width="1.5"/>
  <path d="M243 192 L269 192 L266 214 C262 222 262 230 266 238 L262 392 L256 432 L250 392 L246 238 C250 230 250 222 246 214z"
        fill="url(#metalV)" stroke="{ink}" stroke-width="1.5"/>
  <path d="M256 200 C246 212 246 226 256 236 C266 226 266 212 256 200z M256 244 L256 386" fill="none" stroke="{ink}" stroke-width="2" opacity=".6"/>
</g>'''

LETTERS = 'font-family="\'Cinzel Decorative\', \'Cinzel\', \'Trajan Pro\', \'Times New Roman\', Georgia, serif" font-weight="700"'

def seal(t):
    ink = t['ink']
    glow = ' filter="url(#glow)"' if t['glow'] else ''
    ring_text = f'font-family="\'Cinzel\', \'Trajan Pro\', \'Times New Roman\', Georgia, serif" font-weight="700" fill="url(#metal)" letter-spacing="2"'
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-labelledby="t">
<title id="t">HRMS by Durolord</title>
{defs(t)}
<circle cx="256" cy="256" r="252" fill="url(#face)"/>
<g{glow}>
  <circle cx="256" cy="256" r="246" fill="none" stroke="url(#metal)" stroke-width="7"/>
  <circle cx="256" cy="256" r="234" fill="none" stroke="url(#metal)" stroke-width="2"/>
  <circle cx="256" cy="256" r="186" fill="none" stroke="url(#metal)" stroke-width="4"/>
</g>
<path id="top" d="M58 256 A198 198 0 0 1 454 256" fill="none"/>
<path id="bottom" d="M36 256 A220 220 0 0 0 476 256" fill="none"/>
<text font-size="25" {ring_text}><textPath href="#top" startOffset="50%" text-anchor="middle">DUROLORD • HRMS • PEOPLE &amp; PAYROLL</textPath></text>
<text font-size="18.5" {ring_text}><textPath href="#bottom" startOffset="50%" text-anchor="middle">SEEKER OF TALENT • FORGED IN LIGHT AND DARKNESS</textPath></text>
<circle cx="45" cy="256" r="4.5" fill="url(#metal)"/><circle cx="467" cy="256" r="4.5" fill="url(#metal)"/>
<g{glow}>
  <text x="158" y="318" font-size="168" text-anchor="middle" {LETTERS} fill="url(#metalV)" stroke="{ink}" stroke-width="2.5" paint-order="stroke">H</text>
  <text x="356" y="318" font-size="168" text-anchor="middle" {LETTERS} fill="url(#metalV)" stroke="{ink}" stroke-width="2.5" paint-order="stroke">R</text>
  {sword(t)}
</g>
</svg>
'''

def favicon(t):
    # Small sizes: no ring text, heavier rings and letters so it stays legible at 16-32px.
    ink = t['ink']
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
{defs(t)}
<circle cx="256" cy="256" r="250" fill="url(#face)"/>
<circle cx="256" cy="256" r="236" fill="none" stroke="url(#metal)" stroke-width="22"/>
<text x="148" y="338" font-size="230" text-anchor="middle" {LETTERS} fill="url(#metalV)" stroke="{ink}" stroke-width="4" paint-order="stroke">H</text>
<text x="366" y="338" font-size="230" text-anchor="middle" {LETTERS} fill="url(#metalV)" stroke="{ink}" stroke-width="4" paint-order="stroke">R</text>
<g transform="translate(0 -40) scale(1 1.16) translate(0 4)">{sword(t, 1.5)}</g>
</svg>
'''

for name, t in THEMES.items():
    open(os.path.join(out, f'logo-{name}.svg'), 'w').write(seal(t))
    open(os.path.join(out, f'favicon-{name}.svg'), 'w').write(favicon(t))
print('ok')
