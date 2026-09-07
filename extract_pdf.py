import pypdf

path = r'c:/Users/CASA/Downloads/Valsoft Library assignment-.pdf'
reader = pypdf.PdfReader(path)
print('PAGES:', len(reader.pages))
for i, page in enumerate(reader.pages):
    print('--- PAGE %d ---' % (i + 1))
    text = page.extract_text() or ''
    print(text)
