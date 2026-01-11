import { useTranslation } from "react-i18next";

type ConfirmationDialogProps = {
  message: string;
  onConfirm: () => void;
  onCancel: () => void;
};

export default function ConfirmationDialog({
  message,
  onConfirm,
  onCancel,
}: ConfirmationDialogProps) {
  const { t } = useTranslation(["table"]);
  return (
    <div className="popup">
      <div className="popup-content">
        <p>{message}</p>
        <div className="popup-actions">
          <button className="button button-primary" onClick={onConfirm}>
            {t("confirmDelete.confirmButton")}
          </button>
          <button className="button button-outline" onClick={onCancel}>
            {t("confirmDelete.cancelButton")}
          </button>
        </div>
      </div>
    </div>
  );
}
