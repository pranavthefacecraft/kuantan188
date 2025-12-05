import React, { useState } from 'react';
import { Dropdown } from 'react-bootstrap';
import { 
  generateShareUrls, 
  copyToClipboard, 
  canUseNativeShare, 
  nativeShare,
  generateTicketShareData
} from '../utils/shareUtils';

interface ShareButtonProps {
  ticket: any;
  className?: string;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'light' | 'dark' | 'primary';
}

const ShareButton: React.FC<ShareButtonProps> = ({ 
  ticket, 
  className = '',
  size = 'sm',
  variant = 'light'
}) => {
  const [showCopySuccess, setShowCopySuccess] = useState(false);
  const [isSharing, setIsSharing] = useState(false);

  const shareData = generateTicketShareData(ticket);
  const shareUrls = generateShareUrls(shareData);

  const handleSocialShare = (platform: string, url: string) => {
    window.open(url, '_blank', 'width=600,height=400,scrollbars=yes,resizable=yes');
  };

  const handleCopyLink = async () => {
    const success = await copyToClipboard(shareData.url);
    if (success) {
      setShowCopySuccess(true);
      setTimeout(() => setShowCopySuccess(false), 2000);
    }
  };

  const handleNativeShare = async () => {
    setIsSharing(true);
    const success = await nativeShare(shareData);
    setIsSharing(false);
    
    if (!success && canUseNativeShare()) {
      // If native share failed but is available, user probably cancelled
      console.log('Native share was cancelled by user');
    }
  };

  const getButtonSize = () => {
    switch (size) {
      case 'lg': return 'btn-lg';
      case 'md': return '';
      case 'sm': 
      default: return 'btn-sm';
    }
  };

  const getButtonVariant = () => {
    switch (variant) {
      case 'dark': return 'btn-outline-dark';
      case 'primary': return 'btn-outline-primary';
      case 'light': 
      default: return 'btn-outline-light';
    }
  };

  return (
    <Dropdown className={className}>
      <Dropdown.Toggle 
        variant="link" 
        className={`share-button border-0 p-2 ${getButtonVariant()} ${getButtonSize()}`}
        style={{
          backgroundColor: 'rgba(255, 255, 255, 0.1)',
          backdropFilter: 'blur(10px)',
          border: '1px solid rgba(255, 255, 255, 0.2) !important',
        }}
        disabled={isSharing}
      >
        <i className="fas fa-share-alt text-white"></i>
      </Dropdown.Toggle>

      <Dropdown.Menu 
        className="share-dropdown-menu border-0 shadow-lg"
        style={{
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          backdropFilter: 'blur(10px)',
          minWidth: '200px',
        }}
      >
        <div className="px-3 py-2 border-bottom">
          <small className="text-muted fw-bold">Share this ticket</small>
        </div>

        {/* Native Share (Mobile) */}
        {canUseNativeShare() && (
          <Dropdown.Item 
            onClick={handleNativeShare}
            className="d-flex align-items-center py-2"
            disabled={isSharing}
          >
            <i className="fas fa-mobile-alt me-3 text-primary"></i>
            <span>Share via Mobile</span>
          </Dropdown.Item>
        )}

        {/* Social Media Platforms */}
        <Dropdown.Item 
          onClick={() => handleSocialShare('Facebook', shareUrls.facebook)}
          className="d-flex align-items-center py-2"
        >
          <i className="fab fa-facebook-f me-3" style={{ color: '#1877F2' }}></i>
          <span>Facebook</span>
        </Dropdown.Item>

        <Dropdown.Item 
          onClick={() => handleSocialShare('Twitter', shareUrls.twitter)}
          className="d-flex align-items-center py-2"
        >
          <i className="fab fa-twitter me-3" style={{ color: '#1DA1F2' }}></i>
          <span>Twitter</span>
        </Dropdown.Item>

        <Dropdown.Item 
          onClick={() => handleSocialShare('WhatsApp', shareUrls.whatsapp)}
          className="d-flex align-items-center py-2"
        >
          <i className="fab fa-whatsapp me-3" style={{ color: '#25D366' }}></i>
          <span>WhatsApp</span>
        </Dropdown.Item>

        <Dropdown.Item 
          onClick={() => handleSocialShare('LinkedIn', shareUrls.linkedin)}
          className="d-flex align-items-center py-2"
        >
          <i className="fab fa-linkedin-in me-3" style={{ color: '#0A66C2' }}></i>
          <span>LinkedIn</span>
        </Dropdown.Item>

        <Dropdown.Item 
          onClick={() => handleSocialShare('Telegram', shareUrls.telegram)}
          className="d-flex align-items-center py-2"
        >
          <i className="fab fa-telegram-plane me-3" style={{ color: '#0088cc' }}></i>
          <span>Telegram</span>
        </Dropdown.Item>

        <Dropdown.Divider />

        {/* Copy Link */}
        <Dropdown.Item 
          onClick={handleCopyLink}
          className="d-flex align-items-center py-2"
        >
          <i className="fas fa-copy me-3 text-secondary"></i>
          <span>{showCopySuccess ? 'Link Copied!' : 'Copy Link'}</span>
          {showCopySuccess && (
            <span className="ms-auto">
              <small className="text-success">✓</small>
            </span>
          )}
        </Dropdown.Item>
      </Dropdown.Menu>
    </Dropdown>
  );
};

export default ShareButton;