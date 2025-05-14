<form id="deleteForm{{ $id }}" action="{{ $route }}" method="POST" style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="button" class="delete-trigger action-icon delete-icon" 
            data-form-id="deleteForm{{ $id }}"
            data-message="{{ isset($message) ? $message : 'Bạn có chắc chắn muốn xóa mục này?' }}" 
            title="Xóa">
        <i class="fas fa-trash-alt"></i>
    </button>
</form>

@once
<div class="delete-modal" id="deleteConfirmModal">
    <div class="delete-modal-overlay"></div>
    <div class="delete-modal-container">
        <div class="delete-modal-header">
            <div class="delete-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Xác nhận xóa</h3>
            <button type="button" class="delete-modal-close" id="closeDeleteModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="delete-modal-body">
            <p id="deleteConfirmMessage"></p>
            <div class="delete-modal-warning">
                <i class="fas fa-info-circle"></i>
                <span>Lưu ý: Hành động này không thể hoàn tác sau khi thực hiện.</span>
            </div>
        </div>
        <div class="delete-modal-footer">
            <button type="button" class="delete-modal-cancel" id="cancelDelete">
                <i class="fas fa-times"></i> Hủy
            </button>
            <button type="button" class="delete-modal-confirm" id="confirmDelete">
                <i class="fas fa-trash-alt"></i> Xóa
            </button>
        </div>
    </div>
</div>

<style>
    .delete-icon {
        background-color: #dc3545;
    }
    
    .delete-icon:hover {
        background-color: #c82333;
        box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        color: white;
    }

    .delete-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
    }
    
    .delete-modal.show {
        display: block;
        animation: fadeIn 0.2s ease-out;
    }
    
    .delete-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .delete-modal-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 400px;
        max-width: 90%;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }
    
    .delete-modal-header {
        position: relative;
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .delete-modal-icon {
        width: 50px;
        height: 50px;
        margin: 0 auto 15px;
        background-color: #fff3f3;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .delete-modal-icon i {
        font-size: 24px;
        color: #dc3545;
    }
    
    .delete-modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    
    .delete-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 18px;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .delete-modal-close:hover {
        color: #333;
    }
    
    .delete-modal-body {
        padding: 20px;
        text-align: center;
    }
    
    .delete-modal-body p {
        margin-bottom: 15px;
        font-size: 15px;
        color: #444;
    }
    
    .delete-modal-warning {
        display: flex;
        align-items: flex-start;
        background-color: #fff9ec;
        padding: 10px 15px;
        border-radius: 6px;
        margin-top: 15px;
        text-align: left;
    }
    
    .delete-modal-warning i {
        color: #f0ad4e;
        margin-right: 10px;
        font-size: 14px;
        margin-top: 2px;
    }
    
    .delete-modal-warning span {
        font-size: 13px;
        color: #6c757d;
    }
    
    .delete-modal-footer {
        display: flex;
        padding: 15px 20px;
        gap: 10px;
        border-top: 1px solid #f1f1f1;
    }
    
    .delete-modal-cancel, .delete-modal-confirm {
        flex: 1;
        padding: 10px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 14px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .delete-modal-cancel {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        color: #555;
    }
    
    .delete-modal-cancel:hover {
        background-color: #e9ecef;
        color: #333;
    }
    
    .delete-modal-confirm {
        background-color: #dc3545;
        color: white;
    }
    
    .delete-modal-confirm:hover {
        background-color: #c82333;
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.2);
    }
    
    .delete-modal-cancel i, .delete-modal-confirm i {
        margin-right: 6px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @media (max-width: 576px) {
        .delete-modal-container {
            width: 90%;
        }
        
        .delete-modal-footer {
            flex-direction: column;
        }
        
        .delete-modal-cancel, .delete-modal-confirm {
            width: 100%;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Khai báo biến lưu form ID hiện tại
        let currentFormId = '';
        
        // Tất cả các nút xóa
        const deleteTriggers = document.querySelectorAll('.delete-trigger');
        const deleteModal = document.getElementById('deleteConfirmModal');
        const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');
        const closeDeleteBtn = document.getElementById('closeDeleteModal');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const modalOverlay = document.querySelector('.delete-modal-overlay');
        
        // Mở modal xóa
        deleteTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                currentFormId = this.getAttribute('data-form-id');
                const message = this.getAttribute('data-message');
                deleteConfirmMessage.textContent = message;
                deleteModal.classList.add('show');
            });
        });
        
        // Đóng modal khi click vào nút đóng
        function closeModal() {
            deleteModal.classList.remove('show');
        }
        
        closeDeleteBtn.addEventListener('click', closeModal);
        cancelDeleteBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);
        
        // Xác nhận xóa
        confirmDeleteBtn.addEventListener('click', function() {
            if (currentFormId) {
                const form = document.getElementById(currentFormId);
                if (form) {
                    form.submit();
                }
            }
            closeModal();
        });
        
        // Ngăn sự kiện nổi bọt từ container
        document.querySelector('.delete-modal-container').addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Bắt phím ESC để đóng modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteModal.classList.contains('show')) {
                closeModal();
            }
        });
    });
</script>
@endpush
@endonce