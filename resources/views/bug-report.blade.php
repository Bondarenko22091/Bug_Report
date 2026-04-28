<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание баг-репорта</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 750px;
            margin: 40px auto;
            padding: 20px;
            background: #f0f2f5;
        }
        .container {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        h1 {
            margin-top: 0;
            color: #1a1a1a;
            font-size: 24px;
        }
        label {
            display: block;
            margin-top: 20px;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
            font-family: "SF Mono", "Fira Code", monospace;
            font-size: 13px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .file-upload-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .file-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            color: #475569;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .file-upload-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }
        .file-upload-btn svg {
            width: 18px;
            height: 18px;
        }
        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
        }
        .file-list .file-tag {
            background: #e0e7ff;
            color: #3730a3;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .file-list .file-tag .remove-file {
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            line-height: 1;
            color: #6366f1;
        }
        .file-list .file-tag .remove-file:hover {
            color: #ef4444;
        }
        .small {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        button[type="submit"] {
            margin-top: 25px;
            padding: 14px 32px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        button[type="submit"]:hover {
            background: #1d4ed8;
        }
        button[type="submit"]:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        button.loading .spinner {
            display: inline-block;
        }
        button.loading .btn-text {
            display: none;
        }

        .success {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            border-left: 4px solid #10b981;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .success a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .success a:hover {
            text-decoration: underline;
        }
        .error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-left: 4px solid #ef4444;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #991b1b;
            font-size: 14px;
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }

        .close-btn {
        cursor: pointer;
        font-size: 20px;
        color: #94a3b8;
        padding: 0 4px;
        line-height: 1;
        transition: color 0.2s;
        }
        .close-btn:hover {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Создать баг-репорт</h1>

        @if (session('success'))
            <div class="success" id="success-message">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <strong>Баг-репорт создан!</strong><br>
                        <a href="{{ session('success')['url'] }}" target="_blank">
                            {{ session('success')['title'] }}
                        </a><br>
                        <small style="color:#64748b;">
                            Критичность: <strong>{{ strtoupper(session('success')['severity']) }}</strong> |
                            Шагов: {{ session('success')['steps_count'] }} |
                            Изображений: {{ session('success')['images_count'] }} |
                            PDF: {{ session('success')['documents_count'] }}
                        </small>
                    </div>
                    <span class="close-btn" onclick="document.getElementById('success-message').remove()" title="Закрыть">
                        ✕
                    </span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form id="bug-form" action="{{ route('issues.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="text">Текст ошибки (лог):</label>
            <textarea 
                id="text" 
                name="text" 
                placeholder="Вставьте лог ошибки или описание проблемы..."
                required
            >{{ old('text') }}</textarea>

            <hr class="divider">

            <label>Скриншоты (JPEG, PNG, до 5 шт.):</label>
            <div class="file-upload-wrapper" id="images-wrapper">
                <label class="file-upload-btn" for="images-input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Выбрать скриншоты
                </label>
                <input 
                    type="file" 
                    id="images-input" 
                    accept="image/jpeg,image/png,image/jpg"
                    multiple
                    style="position: absolute; opacity: 0; width: 0; height: 0;"
                >
                <div class="file-list" id="images-list"></div>
            </div>
            <div class="small">Максимум 5 файлов, каждый до 2 МБ</div>

            <label>PDF-документы (до 3 шт.):</label>
            <div class="file-upload-wrapper" id="documents-wrapper">
                <label class="file-upload-btn" for="documents-input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Выбрать PDF
                </label>
                <input 
                    type="file" 
                    id="documents-input" 
                    accept="application/pdf"
                    multiple
                    style="position: absolute; opacity: 0; width: 0; height: 0;"
                >
                <div class="file-list" id="documents-list"></div>
            </div>
            <div class="small">Максимум 3 файла, каждый до 5 МБ</div>

            <button type="submit" id="submit-btn">
                <span class="btn-text">Отправить баг-репорт</span>
                <div class="spinner"></div>
            </button>
        </form>
    </div>

    <script>

        const selectedImages = [];
        const selectedDocuments = [];

        const MAX_IMAGES = 5;
        const MAX_DOCUMENTS = 3;

        const form = document.getElementById('bug-form');
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        const imagesInput = document.getElementById('images-input');
        const imagesList = document.getElementById('images-list');

        imagesInput.addEventListener('change', function() {
            for (const file of this.files) {
                if (selectedImages.length >= MAX_IMAGES) {
                    alert('Максимум ' + MAX_IMAGES + ' скриншотов');
                    break;
                }
                const exists = selectedImages.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    selectedImages.push(file);
                }
            }
            renderImageList();
            updateFormInputs();
            this.value = '';
        });

        function renderImageList() {
            imagesList.innerHTML = '';
            selectedImages.forEach((file, index) => {
                const tag = document.createElement('span');
                tag.className = 'file-tag';
                tag.innerHTML = `
                    ${file.name}
                    <span class="remove-file" onclick="removeImage(${index})">&times;</span>
                `;
                imagesList.appendChild(tag);
            });
        }

        function removeImage(index) {
            selectedImages.splice(index, 1);
            renderImageList();
            updateFormInputs();
        }

        const documentsInput = document.getElementById('documents-input');
        const documentsList = document.getElementById('documents-list');

        documentsInput.addEventListener('change', function() {
            for (const file of this.files) {
                if (selectedDocuments.length >= MAX_DOCUMENTS) {
                    alert('Максимум ' + MAX_DOCUMENTS + ' PDF-документов');
                    break;
                }
                const exists = selectedDocuments.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    selectedDocuments.push(file);
                }
            }
            renderDocumentList();
            updateFormInputs();
            this.value = '';
        });

        function renderDocumentList() {
            documentsList.innerHTML = '';
            selectedDocuments.forEach((file, index) => {
                const tag = document.createElement('span');
                tag.className = 'file-tag';
                tag.innerHTML = `
                    ${file.name}
                    <span class="remove-file" onclick="removeDocument(${index})">&times;</span>
                `;
                documentsList.appendChild(tag);
            });
        }

        function removeDocument(index) {
            selectedDocuments.splice(index, 1);
            renderDocumentList();
            updateFormInputs();
        }

        function updateFormInputs() {
            document.querySelectorAll('.hidden-file-input').forEach(el => el.remove());

            const formEl = document.getElementById('bug-form');

            if (selectedImages.length > 0) {
                const dt = new DataTransfer();
                selectedImages.forEach(f => dt.items.add(f));
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'images[]';
                input.className = 'hidden-file-input';
                input.files = dt.files;
                input.style.display = 'none';
                formEl.appendChild(input);
            }

            if (selectedDocuments.length > 0) {
                const dt = new DataTransfer();
                selectedDocuments.forEach(f => dt.items.add(f));
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'documents[]';
                input.className = 'hidden-file-input';
                input.files = dt.files;
                input.style.display = 'none';
                formEl.appendChild(input);
            }
        }
    </script>
</body>
</html>