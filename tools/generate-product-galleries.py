from PIL import Image, ImageDraw, ImageFont, ImageFilter
import os, math

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'assets', 'images', 'products')
os.makedirs(OUT, exist_ok=True)

W, H = 1200, 900
NAVY=(4,20,36); NAVY2=(8,33,54); TEAL=(16,190,170); WHITE=(246,249,252)
MUTED=(183,200,211); PINK=(225,48,112); YELLOW=(253,190,44); BLUE=(36,95,120)

FONT_BOLD='/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'
FONT_REG='/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'
FB=lambda s: ImageFont.truetype(FONT_BOLD,s)
FR=lambda s: ImageFont.truetype(FONT_REG,s)


def rounded(draw, box, radius, fill, outline=None, width=1):
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline, width=width)


def logo(draw, x, y, scale=1.0, dark_bg=True):
    bw=int(42*scale); bh=max(5,int(10*scale)); gap=max(3,int(5*scale))
    for i,c in enumerate([PINK,YELLOW,BLUE]):
        draw.rounded_rectangle([x,y+i*(bh+gap),x+bw,y+i*(bh+gap)+bh],radius=max(2,int(2*scale)),fill=c)
    color=WHITE if dark_bg else NAVY
    draw.multiline_text((x+bw+int(12*scale),y-int(3*scale)),'Print\nBureau',font=FB(max(12,int(24*scale))),fill=color,spacing=max(-3,int(-3*scale)))


def add_shadow(canvas, rect, radius=18, offset=(12,16), opacity=90):
    x0,y0,x1,y1=rect
    layer=Image.new('RGBA',canvas.size,(0,0,0,0)); d=ImageDraw.Draw(layer)
    d.rounded_rectangle([x0+offset[0],y0+offset[1],x1+offset[0],y1+offset[1]],radius=radius,fill=(0,0,0,opacity))
    canvas.alpha_composite(layer.filter(ImageFilter.GaussianBlur(16)))


def paper_card(canvas, rect, fill=WHITE, radius=14, angle=0, darktext=True, accent=True):
    x0,y0,x1,y1=rect; w=x1-x0; h=y1-y0
    card=Image.new('RGBA',(w+80,h+80),(0,0,0,0)); d=ImageDraw.Draw(card)
    d.rounded_rectangle([35,40,w+35,h+40],radius=radius,fill=(0,0,0,75))
    d.rounded_rectangle([20,20,w+20,h+20],radius=radius,fill=fill,outline=(210,218,225),width=1)
    if accent: d.rounded_rectangle([20,20,28,h+20],radius=radius,fill=TEAL)
    logo(d,44,44,.55,dark_bg=(sum(fill[:3])<350))
    tc=(60,76,90) if darktext else (210,222,230)
    for i,frac in enumerate([.35,.52,.44]):
        yy=int(h*.62)+i*20; d.rounded_rectangle([44,yy,44+int(w*frac),yy+5],radius=2,fill=tc)
    if angle: card=card.rotate(angle,resample=Image.Resampling.BICUBIC,expand=True)
    canvas.alpha_composite(card,(int((x0+x1-card.width)/2),int((y0+y1-card.height)/2)))


def base_scene(title, subtitle, idx):
    img=Image.new('RGBA',(W,H),NAVY); bg=Image.new('RGBA',(W,H),(0,0,0,0)); bd=ImageDraw.Draw(bg)
    for i in range(0,H,30): bd.rectangle([0,i,W,i+30],fill=(0,60,90,max(0,40-int(i/30))))
    for x in range(0,W,120): bd.line((x,0,x,H),fill=(255,255,255,10),width=1)
    for y in range(0,H,120): bd.line((0,y,W,y),fill=(255,255,255,10),width=1)
    img.alpha_composite(bg); d=ImageDraw.Draw(img)
    logo(d,60,45,.9,True); d.text((60,150),title,font=FB(44),fill=WHITE); d.text((60,206),subtitle,font=FB(18),fill=MUTED)
    rounded(d,[60,760,360,810],25,(10,47,69),outline=(30,86,103)); d.text((82,774),f'Premium product view {idx}',font=FB(18),fill=(197,222,225))
    return img


def business_cards(img,i):
    d=ImageDraw.Draw(img)
    if i==1: paper_card(img,(530,260,970,520),(18,38,58),angle=-6,darktext=False); paper_card(img,(470,330,910,590),WHITE,angle=5)
    elif i==2:
        for k in range(5): paper_card(img,(540+k*9,310+k*7,950+k*9,545+k*7),WHITE if k<4 else (18,38,58),angle=-4+k*1.5,darktext=k<4)
    elif i==3: paper_card(img,(430,220,1030,610),(18,38,58),angle=-10,darktext=False); d.ellipse([900,200,1040,340],outline=TEAL,width=5); d.text((914,250),'MATTE',font=FB(18),fill=TEAL)
    else: add_shadow(img,(510,260,1020,650),24,(18,22),110); rounded(d,[510,260,1020,650],26,(12,31,47),outline=(50,76,92)); rounded(d,[555,310,975,600],20,(22,47,65)); paper_card(img,(600,355,930,550),WHITE)


def flyers(img,i):
    d=ImageDraw.Draw(img)
    if i==1:
        for k,a in enumerate([-10,0,9]): paper_card(img,(520+k*55,240,900+k*55,690),WHITE,angle=a)
    elif i==2: paper_card(img,(470,215,990,720),WHITE,angle=2); rounded(d,[560,360,900,500],18,(230,241,245)); d.text((610,405),'PROMOTION',font=FB(28),fill=NAVY)
    elif i==3:
        for k in range(4): paper_card(img,(520+k*35,270+k*22,920+k*35,650+k*22),(244,248,251),angle=-6+k*3)
    else: paper_card(img,(520,250,960,680),(240,246,249),angle=-7); rounded(d,[920,250,1040,560],14,(14,42,62)); d.text((938,310),'A5',font=FB(28),fill=TEAL)


def packaging(img,i):
    d=ImageDraw.Draw(img)
    if i in (1,3):
        for x,y,s in [(500,330,230),(760,250,260)]: add_shadow(img,(x,y,x+s,y+s),20,(18,22),100); rounded(d,[x,y,x+s,y+s],20,(214,184,132) if i==1 else (20,45,62)); logo(d,x+35,y+35,.6,dark_bg=i!=1)
    elif i==2: rounded(d,[500,270,980,650],30,(21,44,60),outline=(52,82,96)); rounded(d,[555,320,925,600],18,(233,229,216)); logo(d,620,385,.85,False); d.text((610,520),'Premium Packaging',font=FB(18),fill=NAVY)
    else: rounded(d,[520,320,1000,600],24,(212,181,125)); rounded(d,[650,300,870,620],18,(18,43,61)); logo(d,690,400,.7,True)


def stationery(img,i):
    d=ImageDraw.Draw(img)
    if i==1: paper_card(img,(520,230,980,700),WHITE,angle=-4); paper_card(img,(760,420,1050,650),(18,38,58),angle=5,darktext=False)
    elif i==2: add_shadow(img,(520,330,1010,620),16,(14,18),90); rounded(d,[520,330,1010,620],18,WHITE); d.polygon([(520,330),(765,520),(1010,330)],fill=(232,239,244)); logo(d,590,390,.6,False)
    elif i==3: paper_card(img,(520,240,900,720),WHITE,angle=-7); d.rounded_rectangle([930,300,960,680],radius=12,fill=(19,51,69)); d.polygon([(930,680),(960,680),(945,730)],fill=(210,216,220))
    else:
        for k in range(3): paper_card(img,(520+k*80,280+k*15,890+k*80,670+k*15),WHITE,angle=-5+k*4)


def notebooks(img,i):
    d=ImageDraw.Draw(img)
    if i==1: add_shadow(img,(520,240,930,700),24,(18,20),100); rounded(d,[520,240,930,700],24,(18,39,56)); logo(d,590,330,.9,True); d.text((590,520),'NOTES',font=FB(28),fill=MUTED); [d.ellipse([500,y,532,y+8],fill=(190,198,204)) for y in range(280,680,34)]
    elif i==2:
        for k in range(3): rounded(d,[520+k*60,300+k*20,900+k*60,690+k*20],20,(28+10*k,50+8*k,64+7*k)); logo(d,570+k*60,350+k*20,.55,True)
    elif i==3: rounded(d,[490,250,970,720],20,(247,249,250)); d.line((730,260,730,710),fill=(200,210,218),width=3); [d.line((530,y,690,y),fill=(190,205,215),width=2) for y in range(320,670,36)]; [d.line((770,y,930,y),fill=(190,205,215),width=2) for y in range(320,670,36)]; logo(d,560,280,.45,False)
    else: rounded(d,[520,270,950,700],22,(30,48,62)); rounded(d,[560,310,910,660],16,(20,38,52)); d.rectangle([700,270,760,700],fill=(39,52,59)); logo(d,600,380,.6,True)


def books(img,i):
    d=ImageDraw.Draw(img)
    if i==1:
        for k in range(4): rounded(d,[520+k*18,430-k*45,970+k*18,650-k*45],18,(245-8*k,247-6*k,248-5*k)); logo(d,570+k*18,475-k*45,.5,False)
    elif i==2: rounded(d,[500,260,980,700],22,(18,40,58)); logo(d,570,330,.8,True); d.text((570,520),'CATALOG',font=FB(28),fill=MUTED)
    elif i==3: rounded(d,[470,240,1010,710],20,WHITE); d.line((740,250,740,700),fill=(205,214,220),width=3); [d.rounded_rectangle([x,y,x+170,y+8],radius=4,fill=(180,197,208)) for x in (510,780) for y in (340,390,440,490)]; logo(d,510,280,.45,False)
    else: rounded(d,[520,250,940,700],22,(244,246,247)); d.rectangle([520,250,555,700],fill=(18,43,60)); logo(d,610,340,.7,False)


def stickers(img,i):
    d=ImageDraw.Draw(img)
    if i==1: d.ellipse([520,250,930,660],fill=(235,239,241),outline=(190,200,205),width=4); d.ellipse([610,340,840,570],fill=NAVY2); [d.ellipse([725+int(math.cos(math.radians(a))*155)-40,455+int(math.sin(math.radians(a))*155)-28,725+int(math.cos(math.radians(a))*155)+40,455+int(math.sin(math.radians(a))*155)+28],fill=TEAL) for a in range(0,360,45)]
    elif i==2:
        for x,y,c in [(500,280,PINK),(700,230,YELLOW),(820,420,TEAL),(600,500,(70,130,180))]: d.ellipse([x,y,x+180,y+130],fill=c); logo(d,x+30,y+35,.4,dark_bg=c not in (YELLOW,TEAL))
    elif i==3: rounded(d,[480,240,1010,700],20,WHITE); [rounded(d,[530+c*150,300+r*125,640+c*150,370+r*125],16,(225,242,240)) for r in range(3) for c in range(3)]
    else:
        for x in [520,720,900]: rounded(d,[x,330,x+120,650],28,(235,238,239)); rounded(d,[x-5,400,x+125,520],16,(19,52,69)); logo(d,x+18,425,.28,True)


def banners(img,i):
    d=ImageDraw.Draw(img)
    if i==1: rounded(d,[610,210,900,690],12,(240,246,248)); d.rectangle([630,670,880,700],fill=(35,51,62)); d.line((755,700,755,760),fill=(200,210,215),width=10); logo(d,670,320,.65,False); d.text((655,520),'PRINT BIG',font=FB(28),fill=NAVY)
    elif i==2: d.line((480,240,480,690),fill=(120,135,145),width=12); d.line((1020,240,1020,690),fill=(120,135,145),width=12); rounded(d,[500,280,1000,610],16,(16,48,66)); logo(d,620,365,.85,True); d.text((620,500),'BRAND DISPLAY',font=FB(18),fill=TEAL)
    elif i==3:
        for k,x in enumerate([500,690,880]): rounded(d,[x,260,x+160,620],10,WHITE); d.rectangle([x+18,300,x+142,420],fill=[PINK,YELLOW,TEAL][k]); d.text((x+32,500),'POSTER',font=FB(18),fill=NAVY)
    else: rounded(d,[480,260,1020,650],18,(247,249,250)); logo(d,570,340,1,False); d.text((570,530),'EVENT BACKDROP',font=FB(28),fill=NAVY)


def institutional(img,i):
    d=ImageDraw.Draw(img)
    if i==1:
        for k in range(4): paper_card(img,(500+k*40,280+k*22,900+k*40,650+k*22),WHITE,angle=-4+k*2)
    elif i==2: d.line((760,210,650,350),fill=TEAL,width=16); d.line((760,210,870,350),fill=TEAL,width=16); rounded(d,[620,330,900,680],24,WHITE); logo(d,670,390,.55,False); d.ellipse([715,485,805,575],fill=(195,210,220)); d.text((690,610),'STUDENT ID',font=FB(18),fill=NAVY)
    elif i==3: rounded(d,[520,250,980,700],20,(18,42,59)); logo(d,600,340,.8,True); d.text((600,520),'INSTITUTIONAL\nREPORT',font=FB(28),fill=MUTED,spacing=8)
    else: paper_card(img,(470,250,900,620),WHITE,angle=-6); paper_card(img,(650,330,1050,690),WHITE,angle=5)


def certificates(img,i):
    d=ImageDraw.Draw(img)
    if i==1: rounded(d,[480,240,1010,680],12,(249,247,239),outline=(198,176,109),width=5); d.rectangle([520,280,970,640],outline=(198,176,109),width=2); d.text((610,350),'CERTIFICATE',font=FB(28),fill=NAVY); logo(d,670,520,.45,False)
    elif i==2: rounded(d,[520,270,970,650],12,(250,249,245),outline=(201,177,100),width=4); d.ellipse([820,500,900,580],fill=(190,40,60)); d.polygon([(842,568),(860,650),(875,585),(900,650),(885,568)],fill=(190,40,60)); d.text((600,380),'ACHIEVEMENT',font=FB(28),fill=NAVY)
    elif i==3: paper_card(img,(480,250,930,650),(250,248,240),angle=-5); paper_card(img,(650,300,1050,680),(250,248,240),angle=6)
    else: rounded(d,[480,240,1010,680],16,(20,43,59)); rounded(d,[530,290,960,630],12,(248,247,239)); d.text((620,380),'AWARD',font=FB(28),fill=NAVY); logo(d,660,520,.5,False)


def invitations(img,i):
    d=ImageDraw.Draw(img)
    if i==1: paper_card(img,(520,250,950,690),(248,244,236),angle=-5); d.ellipse([830,520,930,620],fill=(196,164,98))
    elif i==2: rounded(d,[500,360,1010,660],16,(240,230,214)); d.polygon([(500,360),(755,550),(1010,360)],fill=(228,214,195)); paper_card(img,(580,230,930,580),(250,246,238),angle=3)
    elif i==3:
        for k,c in enumerate([(250,246,238),(235,228,216),(18,42,58)]): paper_card(img,(500+k*65,270+k*30,880+k*65,650+k*30),c,angle=-6+k*4,darktext=k<2)
    else: rounded(d,[500,250,1000,700],24,(17,40,57)); paper_card(img,(590,310,910,640),(250,246,238)); d.ellipse([815,560,900,645],fill=(196,164,98))


def calendars(img,i):
    d=ImageDraw.Draw(img)
    if i==1: d.polygon([(530,650),(650,280),(900,280),(1010,650)],fill=(25,46,60)); rounded(d,[610,300,930,590],16,WHITE); d.text((670,360),'2027',font=FB(28),fill=NAVY); [d.rectangle([650+c*45,430+r*38,675+c*45,450+r*38],fill=(215,224,230)) for r in range(4) for c in range(5)]; [d.ellipse([x,275,x+18,295],fill=(150,160,165)) for x in range(660,900,50)]
    elif i==2: rounded(d,[540,210,930,710],16,WHITE); d.text((640,275),'JANUARY',font=FB(28),fill=NAVY); logo(d,640,570,.6,False); [d.text((585+c*47,370+r*45),str((r*7+c+1)%31+1),font=FR(14),fill=(70,86,100)) for r in range(5) for c in range(7)]
    elif i==3:
        for k in range(4): rounded(d,[500+k*35,300+k*25,940+k*35,660+k*25],16,(250-5*k,250-5*k,248-4*k)); d.text((560+k*35,350+k*25),['JAN','FEB','MAR','APR'][k],font=FB(28),fill=NAVY)
    else: rounded(d,[520,250,970,700],22,(18,42,59)); logo(d,600,340,.8,True); d.text((600,520),'ANNUAL\nPLANNER',font=FB(28),fill=MUTED,spacing=8)


PRODUCTS={
    'business-cards':('Business Cards','Premium visiting cards & finishes',business_cards),
    'flyers-pamphlets':('Flyers & Pamphlets','Campaign, event & promotional print',flyers),
    'packaging':('Packaging','Custom boxes, sleeves & branded packs',packaging),
    'stationery':('Stationery','Letterheads, envelopes & office print',stationery),
    'custom-notebooks-diaries':('Notebooks & Diaries','Custom branded notebooks, diaries & planners',notebooks),
    'books-catalogs':('Books & Catalogs','Catalogs, booklets, reports & books',books),
    'stickers-labels':('Stickers & Labels','Labels, die-cuts & product stickers',stickers),
    'banners-signage':('Banners & Signage','Large-format print, posters & displays',banners),
    'institutional-printing':('Institutional Printing','School, college & recurring institutional print',institutional),
    'certificates':('Certificates','Academic, award & training certificates',certificates),
    'invitations':('Invitations','Wedding, event & premium invitation suites',invitations),
    'calendars':('Calendars','Desk, wall & branded annual calendars',calendars),
}

for slug,(title,subtitle,drawer) in PRODUCTS.items():
    directory=os.path.join(OUT,slug); os.makedirs(directory,exist_ok=True)
    # Do not touch the separately designed Brochures gallery.
    for i in range(1,5):
        image=base_scene(title,subtitle,i); drawer(image,i)
        image.convert('RGB').save(os.path.join(directory,f'{i:02d}-{slug}-view-{i}.webp'),'WEBP',quality=88,method=6)

print(f'Generated {len(PRODUCTS)*4} product gallery images in {OUT}')
