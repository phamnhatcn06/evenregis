<?php

/**
 * MenuHelper - Build sidebar menu tree from permissions
 */
class MenuHelper
{
    /**
     * Menu tree structure definition
     * root => array(label, icon, children[])
     */
    private static $menuTree = array(
        'dashboard' => array(
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'url' => '/admin/default/index',
            'children' => array(),
        ),
        'data-center' => array(
            'label' => 'Quản lý dữ liệu',
            'icon' => 'data',
            'children' => array(),
        ),
        'activities' => array(
            'label' => 'Hoạt động',
            'icon' => 'activities',
            'children' => array(),
        ),
        'reports' => array(
            'label' => 'Báo cáo',
            'icon' => 'reports',
            'children' => array(),
        ),
        'settings' => array(
            'label' => 'Cài đặt',
            'icon' => 'settings',
            'children' => array(),
        ),
    );

    /**
     * Icon SVGs for menu items
     */
    private static $icons = array(
        'dashboard' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"></path></svg>',
        'data' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path d="M18.8088 9.021C18.3573 9.021 17.7592 9.011 17.0146 9.011C15.1987 9.011 13.7055 7.508 13.7055 5.675V2.459C13.7055 2.206 13.5026 2 13.253 2H7.96363C5.49517 2 3.5 4.026 3.5 6.509V17.284C3.5 19.889 5.59022 22 8.16958 22H16.0453C18.5058 22 20.5 19.987 20.5 17.502V9.471C20.5 9.217 20.298 9.012 20.0465 9.013C19.5565 9.016 18.9898 9.021 18.8088 9.021Z" fill="currentColor"></path><path d="M16.0842 2.56729C15.7852 2.25629 15.2632 2.47029 15.2632 2.90129V5.53829C15.2632 6.64429 16.1742 7.55429 17.2802 7.55429C17.9772 7.56229 18.9452 7.56429 19.7672 7.56229C20.1882 7.56129 20.4022 7.05829 20.1102 6.75429C19.0552 5.65729 17.1662 3.69129 16.0842 2.56729Z" fill="currentColor"></path></svg>',
        'activities' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.2428 4.73756C15.2428 6.95855 17.0459 8.75902 19.2702 8.75902C19.5151 8.75782 19.7594 8.73431 20 8.68878V16.6615C20 20.0156 18.0215 22 14.6624 22H7.34636C3.97851 22 2 20.0156 2 16.6615V9.3561C2 6.00195 3.97851 4 7.34636 4H15.3131C15.2659 4.24324 15.2423 4.49054 15.2428 4.73756ZM13.15 14.8966L16.0078 11.2088V11.1912C16.2525 10.8625 16.1901 10.3989 15.8671 10.1463C15.7108 10.0257 15.5122 9.97345 15.3167 10.0016C15.1211 10.0297 14.9453 10.1358 14.8295 10.2956L12.4201 13.3951L9.6766 11.2351C9.51997 11.1131 9.32071 11.0592 9.12381 11.0856C8.92691 11.1121 8.74898 11.2166 8.63019 11.3756L5.67562 15.1863C5.57177 15.3158 5.51586 15.4771 5.51734 15.6429C5.5002 15.9781 5.71187 16.2826 6.03238 16.3838C6.35288 16.485 6.70138 16.3573 6.88031 16.0732L9.35125 12.8771L12.0948 15.0283C12.2508 15.1541 12.4514 15.2111 12.6504 15.1863C12.8494 15.1615 13.0297 15.0569 13.15 14.8966Z" fill="currentColor"></path><circle opacity="0.4" cx="19.2702" cy="4.73756" r="2.73756" fill="currentColor"></circle></svg>',
        'reports' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M11.9912 18.6215L5.49945 21.8641C5.00921 22.1302 4.39768 21.9525 4.12348 21.4643C4.0434 21.3108 4.00106 21.1402 4 20.9668V13.7087C4 14.4283 4.40573 14.8876 5.47299 15.37L11.9912 18.6215Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M8.89526 2H15.0695C18.7984 2 20.0002 3.19779 20.0002 6.91075V20.9668C19.9991 21.1374 19.9568 21.3051 19.8767 21.4554C19.7326 21.7007 19.4949 21.8827 19.2166 21.9598C18.9383 22.0368 18.6404 22.0023 18.3863 21.8641L11.9912 18.6215L5.47299 15.37C4.40573 14.8876 4 14.4283 4 13.7087V6.91075C4 3.19779 5.2018 2 8.89526 2ZM8.22492 9.62227H15.7486C16.1822 9.62227 16.5336 9.26828 16.5336 8.83162C16.5336 8.39495 16.1822 8.04096 15.7486 8.04096H8.22492C7.79137 8.04096 7.43994 8.39495 7.43994 8.83162C7.43994 9.26828 7.79137 9.62227 8.22492 9.62227Z" fill="currentColor"></path></svg>',
        'settings' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M21.25 13.4764C20.429 13.4764 19.761 12.8145 19.761 12.001C19.761 11.1875 20.429 10.5765 21.25 10.5765C21.449 10.5765 21.64 10.4955 21.78 10.3565C21.921 10.2175 22 10.0295 22 9.83247V7.34047C22 5.17347 20.24 3.41647 18.064 3.41647H5.936C3.76 3.41647 2 5.17347 2 7.34047V9.83247C2 10.0295 2.079 10.2175 2.22 10.3565C2.36 10.4955 2.551 10.5765 2.75 10.5765C3.571 10.5765 4.239 11.1875 4.239 12.001C4.239 12.8145 3.571 13.4764 2.75 13.4764C2.336 13.4764 2 13.8095 2 14.2204V16.6595C2 18.8265 3.76 20.5765 5.936 20.5765H18.064C20.24 20.5765 22 18.8265 22 16.6595V14.2204C22 13.8095 21.664 13.4764 21.25 13.4764Z" fill="currentColor"></path></svg>',
        'event' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M16.34 1.99976H7.67C4.28 1.99976 2 4.37976 2 7.91976V16.0898C2 19.6198 4.28 21.9998 7.67 21.9998H16.34C19.73 21.9998 22 19.6198 22 16.0898V7.91976C22 4.37976 19.73 1.99976 16.34 1.99976Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.1246 8.18934C11.1246 8.67134 10.7276 9.06834 10.2456 9.06834C9.76364 9.06834 9.35864 8.67134 9.35864 8.18934C9.35864 7.70734 9.76364 7.30934 10.2456 7.30934C10.7276 7.30934 11.1246 7.70734 11.1246 8.18934ZM14.6406 8.18934C14.6406 8.67134 14.2446 9.06834 13.7616 9.06834C13.2796 9.06834 12.8746 8.67134 12.8746 8.18934C12.8746 7.70734 13.2796 7.30934 13.7616 7.30934C14.2446 7.30934 14.6406 7.70734 14.6406 8.18934ZM15.5956 13.8983C15.6786 14.3483 15.3756 14.7813 14.9256 14.8643C14.8716 14.8733 14.8186 14.8783 14.7656 14.8783C14.3726 14.8783 14.0226 14.6003 13.9486 14.2003C13.7466 13.0553 12.7596 12.2093 11.5956 12.2093H11.5786C10.8946 12.2133 10.2566 12.4843 9.77464 12.9733C9.29264 13.4613 9.03064 14.1033 9.03564 14.7873C9.03564 15.2373 8.66964 15.6033 8.21964 15.6033C7.76964 15.6043 7.40264 15.2393 7.40164 14.7893C7.39364 13.6683 7.83164 12.6153 8.62564 11.8123C9.41964 11.0093 10.4676 10.5593 11.5866 10.5593H11.5996C13.5146 10.5593 15.1866 11.9723 15.5956 13.8983Z" fill="currentColor"></path></svg>',
        'property' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path d="M9.14373 20.7821V17.7152C9.14372 16.9381 9.77567 16.3067 10.5584 16.3018H13.4326C14.2189 16.3018 14.8563 16.9346 14.8563 17.7152V20.7732C14.8562 21.4473 15.404 21.9951 16.0829 22H18.0438C18.9596 22.0024 19.8388 21.6428 20.4872 21.0008C21.1356 20.3588 21.5 19.4869 21.5 18.5775V9.86585C21.5 9.13139 21.1721 8.43471 20.6046 7.9635L13.943 2.67427C12.7785 1.74912 11.1154 1.77901 9.98539 2.74538L3.46701 7.9635C2.87274 8.42082 2.51755 9.11956 2.5 9.86585V18.5686C2.5 20.4637 4.04738 22 5.95617 22H7.87229C8.19917 22.0024 8.51349 21.8751 8.74547 21.6464C8.97746 21.4178 9.10793 21.1067 9.10792 20.7821H9.14373Z" fill="currentColor"></path></svg>',
        'user' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path d="M11.9488 14.54C8.49884 14.54 5.58789 15.1038 5.58789 17.2795C5.58789 19.4562 8.51765 20.0001 11.9488 20.0001C15.3988 20.0001 18.3098 19.4364 18.3098 17.2606C18.3098 15.084 15.38 14.54 11.9488 14.54Z" fill="currentColor"></path><path opacity="0.4" d="M11.949 12.467C14.2851 12.467 16.1583 10.5831 16.1583 8.23351C16.1583 5.88306 14.2851 4 11.949 4C9.61293 4 7.73975 5.88306 7.73975 8.23351C7.73975 10.5831 9.61293 12.467 11.949 12.467Z" fill="currentColor"></path></svg>',
        'registration' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M18.8088 9.021C18.3573 9.021 17.7592 9.011 17.0146 9.011C15.1987 9.011 13.7055 7.508 13.7055 5.675V2.459C13.7055 2.206 13.5026 2 13.253 2H7.96363C5.49517 2 3.5 4.026 3.5 6.509V17.284C3.5 19.889 5.59022 22 8.16958 22H16.0453C18.5058 22 20.5 19.987 20.5 17.502V9.471C20.5 9.217 20.298 9.012 20.0465 9.013C19.5565 9.016 18.9898 9.021 18.8088 9.021Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M8.97398 11.3876H12.359C12.77 11.3876 13.104 11.0546 13.104 10.6436C13.104 10.2316 12.77 9.89758 12.359 9.89758H8.97398C8.56298 9.89758 8.22998 10.2316 8.22998 10.6436C8.22998 11.0546 8.56298 11.3876 8.97398 11.3876ZM8.97408 16.3818H14.4181C14.8291 16.3818 15.1631 16.0488 15.1631 15.6368C15.1631 15.2248 14.8291 14.8918 14.4181 14.8918H8.97408C8.56308 14.8918 8.23008 15.2248 8.23008 15.6368C8.23008 16.0488 8.56308 16.3818 8.97408 16.3818Z" fill="currentColor"></path></svg>',
        'registrationPeriods' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M16.34 1.99976H7.67C4.28 1.99976 2 4.37976 2 7.91976V16.0898C2 19.6198 4.28 21.9998 7.67 21.9998H16.34C19.73 21.9998 22 19.6198 22 16.0898V7.91976C22 4.37976 19.73 1.99976 16.34 1.99976Z" fill="currentColor"></path><path d="M15.395 11.4525L13.225 13.5725L13.715 16.4535C13.795 16.9075 13.605 17.3645 13.224 17.6205C12.844 17.8775 12.354 17.9035 11.945 17.6955L9.404 16.3585L6.86 17.6955C6.683 17.7885 6.487 17.8355 6.293 17.8355C6.052 17.8355 5.811 17.7625 5.604 17.6205C5.223 17.3645 5.033 16.9075 5.113 16.4535L5.603 13.5725L3.433 11.4525C3.098 11.1245 2.984 10.6365 3.135 10.1995C3.287 9.76349 3.677 9.45049 4.137 9.38349L7.05 8.95849L8.32 6.33649C8.529 5.90949 8.947 5.64049 9.404 5.64049H9.405C9.863 5.64149 10.281 5.91149 10.489 6.33949L11.756 8.95849L14.669 9.38349C15.129 9.45049 15.52 9.76349 15.671 10.1995C15.823 10.6365 15.729 11.1245 15.395 11.4525Z" fill="currentColor"></path></svg>',
        'attendee' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path d="M17.8877 10.8967C19.2827 10.7007 20.3567 9.50473 20.3597 8.05573C20.3597 6.62773 19.3187 5.44373 17.9537 5.21973" fill="currentColor"></path><path opacity="0.4" d="M19.7285 14.2505C21.0795 14.4525 22.0225 14.9255 22.0225 15.9005C22.0225 16.5715 21.5785 17.0075 20.8605 17.2815" fill="currentColor"></path><path d="M6.08472 10.8967C4.68972 10.7007 3.61572 9.50473 3.61272 8.05573C3.61272 6.62773 4.65372 5.44373 6.01872 5.21973" fill="currentColor"></path><path opacity="0.4" d="M4.24373 14.2505C2.89273 14.4525 1.94873 14.9255 1.94873 15.9005C1.94873 16.5715 2.39373 17.0075 3.11173 17.2815" fill="currentColor"></path><path d="M11.9727 14.8496C8.60269 14.8496 5.73969 15.3996 5.73969 17.3746C5.73969 19.3496 8.58269 19.9196 11.9727 19.9196C15.3427 19.9196 18.2057 19.3706 18.2057 17.3946C18.2057 15.4186 15.3627 14.8496 11.9727 14.8496Z" fill="currentColor"></path><path opacity="0.4" d="M11.9727 12.5077C14.2867 12.5077 16.1527 10.6417 16.1527 8.32869C16.1527 6.01569 14.2867 4.14868 11.9727 4.14868C9.65972 4.14868 7.79272 6.01569 7.79272 8.32869C7.79272 10.6417 9.65972 12.5077 11.9727 12.5077Z" fill="currentColor"></path></svg>',
        'badge' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M16.191 2H7.81C4.77 2 3 3.78 3 6.83V17.16C3 20.26 4.77 22 7.81 22H16.191C19.28 22 21 20.26 21 17.16V6.83C21 3.78 19.28 2 16.191 2Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M8.07996 6.64999V6.65999C7.64896 6.65999 7.29996 7.00999 7.29996 7.43999C7.29996 7.86999 7.64896 8.21999 8.07996 8.21999H11.069C11.5 8.21999 11.85 7.86999 11.85 7.42899C11.85 6.99999 11.5 6.64999 11.069 6.64999H8.07996ZM15.92 12.74H8.07996C7.64896 12.74 7.29996 12.39 7.29996 11.96C7.29996 11.53 7.64896 11.179 8.07996 11.179H15.92C16.35 11.179 16.7 11.53 16.7 11.96C16.7 12.39 16.35 12.74 15.92 12.74ZM15.92 17.31H8.07996C7.77996 17.35 7.48996 17.2 7.32996 16.95C7.16996 16.69 7.16996 16.36 7.32996 16.11C7.48996 15.85 7.77996 15.71 8.07996 15.74H15.92C16.319 15.78 16.62 16.12 16.62 16.53C16.62 16.929 16.319 17.27 15.92 17.31Z" fill="currentColor"></path></svg>',
        'sport' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="currentColor"></path><path d="M12 7.75C9.66 7.75 7.75 9.66 7.75 12C7.75 14.34 9.66 16.25 12 16.25C14.34 16.25 16.25 14.34 16.25 12C16.25 9.66 14.34 7.75 12 7.75Z" fill="currentColor"></path></svg>',
        'competition' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path d="M15.4315 11.4531L13.2615 13.5731L13.7515 16.4541C13.8315 16.9081 13.6415 17.3651 13.2605 17.6211C12.8805 17.8781 12.3905 17.9041 11.9815 17.6961L9.44053 16.3591L6.89653 17.6961C6.71953 17.7891 6.52353 17.8361 6.32953 17.8361C6.08853 17.8361 5.84753 17.7631 5.64053 17.6211C5.25953 17.3651 5.06953 16.9081 5.14953 16.4541L5.63953 13.5731L3.46953 11.4531C3.13453 11.1251 3.02053 10.6371 3.17153 10.2001C3.32353 9.76411 3.71353 9.45111 4.17353 9.38411L7.08653 8.95911L8.35653 6.33711C8.56553 5.91011 8.98353 5.64111 9.44053 5.64111H9.44153C9.89953 5.64211 10.3175 5.91211 10.5255 6.34011L11.7925 8.95911L14.7055 9.38411C15.1655 9.45111 15.5565 9.76411 15.7075 10.2001C15.8595 10.6371 15.7655 11.1251 15.4315 11.4531Z" fill="currentColor"></path><path opacity="0.4" d="M21.25 13.4764C20.429 13.4764 19.761 12.8145 19.761 12.001C19.761 11.1875 20.429 10.5765 21.25 10.5765C21.449 10.5765 21.64 10.4955 21.78 10.3565C21.921 10.2175 22 10.0295 22 9.83247V7.34047C22 5.17347 20.24 3.41647 18.064 3.41647H5.936C3.76 3.41647 2 5.17347 2 7.34047V9.83247C2 10.0295 2.079 10.2175 2.22 10.3565C2.36 10.4955 2.551 10.5765 2.75 10.5765C3.571 10.5765 4.239 11.1875 4.239 12.001C4.239 12.8145 3.571 13.4764 2.75 13.4764C2.336 13.4764 2 13.8095 2 14.2204V16.6595C2 18.8265 3.76 20.5765 5.936 20.5765H18.064C20.24 20.5765 22 18.8265 22 16.6595V14.2204C22 13.8095 21.664 13.4764 21.25 13.4764Z" fill="currentColor"></path></svg>',
        'meal' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M2 11.0786C2.05 13.4166 2.19 17.4156 2.21 17.8566C2.281 18.7996 2.642 19.7526 3.204 20.4246C3.986 21.3676 4.949 21.7886 6.292 21.7886C8.148 21.7986 10.194 21.7986 12.181 21.7986C14.176 21.7986 16.112 21.7986 17.747 21.7886C19.071 21.7886 20.064 21.3576 20.836 20.4246C21.398 19.7526 21.759 18.7896 21.81 17.8566C21.83 17.4856 21.93 13.1446 21.99 11.0786H2Z" fill="currentColor"></path><path d="M11.2451 15.3843V16.6783C11.2451 17.0923 11.5811 17.4283 11.9951 17.4283C12.4091 17.4283 12.7451 17.0923 12.7451 16.6783V15.3843C12.7451 14.9703 12.4091 14.6343 11.9951 14.6343C11.5811 14.6343 11.2451 14.9703 11.2451 15.3843Z" fill="currentColor"></path></svg>',
        'default' => '<svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20"><path opacity="0.4" d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="currentColor"></path><path d="M12 13.75C12.4142 13.75 12.75 13.4142 12.75 13V8C12.75 7.58579 12.4142 7.25 12 7.25C11.5858 7.25 11.25 7.58579 11.25 8V13C11.25 13.4142 11.5858 13.75 12 13.75Z" fill="currentColor"></path><path d="M12 17C12.5523 17 13 16.5523 13 16C13 15.4477 12.5523 15 12 15C11.4477 15 11 15.4477 11 16C11 16.5523 11.4477 17 12 17Z" fill="currentColor"></path></svg>',
    );

    /**
     * Build menu tree from permissions array
     * @param array $permissions Array of permission items
     * @return array Menu tree with only permitted items
     */
    public static function buildMenuTree($permissions)
    {
        $menu = array();

        // Always add Dashboard
        $menu['dashboard'] = array(
            'label' => 'Dashboard',
            'icon' => self::getIcon('dashboard'),
            'url' => Yii::app()->createUrl('/admin/default/index'),
            'active' => Yii::app()->controller->id == 'default',
            'children' => array(),
        );

        // Group permissions by root
        $grouped = array();
        foreach ($permissions as $perm) {
            $root = isset($perm['root']) ? $perm['root'] : 'other';
            if (!isset($grouped[$root])) {
                $grouped[$root] = array();
            }
            $grouped[$root][] = $perm;
        }

        // Build menu from grouped permissions
        foreach ($grouped as $root => $items) {
            $rootConfig = self::getRootConfig($root);

            if (count($items) == 1 && empty($rootConfig['forceGroup'])) {
                // Single item - add directly without parent
                $item = $items[0];
                $menu[$item['controller']] = self::buildMenuItem($item);
            } else {
                // Multiple items - create group with children
                $children = array();
                foreach ($items as $item) {
                    $children[$item['controller']] = self::buildMenuItem($item);
                }

                $menu[$root] = array(
                    'label' => $rootConfig['label'],
                    'icon' => self::getIcon($rootConfig['icon']),
                    'children' => $children,
                    'active' => self::isGroupActive($children),
                );
            }
        }

        return $menu;
    }

    /**
     * Build single menu item from permission
     */
    private static function buildMenuItem($perm)
    {
        $controller = $perm['controller'];
        $module = isset($perm['module']) ? $perm['module'] : 'admin';
        $url = Yii::app()->createUrl('/' . $module . '/' . $controller . '/admin');

        return array(
            'label' => $perm['name'],
            'icon' => self::getIcon($controller),
            'url' => $url,
            'active' => Yii::app()->controller->id == $controller,
            'controller' => $controller,
            'module' => $module,
            'action' => isset($perm['action']) ? $perm['action'] : '*',
        );
    }

    /**
     * Get root menu configuration
     */
    private static function getRootConfig($root)
    {
        $configs = array(
            'data-center' => array(
                'label' => 'Dữ liệu chung',
                'icon' => 'data',
                'forceGroup' => true,
            ),
            'events' => array(
                'label' => 'Triển khai sự kiện',
                'icon' => 'activities',
                'forceGroup' => true,
            ),
            'activities' => array(
                'label' => 'Hoạt động',
                'icon' => 'activities',
                'forceGroup' => true,
            ),
            'reports' => array(
                'label' => 'Báo cáo',
                'icon' => 'reports',
                'forceGroup' => true,
            ),
            'settings' => array(
                'label' => 'Cài đặt',
                'icon' => 'settings',
                'forceGroup' => true,
            ),
        );

        return isset($configs[$root]) ? $configs[$root] : array(
            'label' => $root,
            'icon' => 'default',
            'forceGroup' => false,
        );
    }

    /**
     * Check if any child in group is active
     */
    private static function isGroupActive($children)
    {
        foreach ($children as $child) {
            if (isset($child['active']) && $child['active']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get icon SVG by name
     */
    public static function getIcon($name)
    {
        return isset(self::$icons[$name]) ? self::$icons[$name] : self::$icons['default'];
    }

    /**
     * Render sidebar menu HTML
     * @param array $menuTree Built menu tree
     * @return string HTML output
     */
    public static function renderMenu($menuTree)
    {
        $html = '';

        foreach ($menuTree as $key => $item) {
            if (!empty($item['children'])) {
                // Has children - render as submenu
                $activeClass = !empty($item['active']) ? '' : 'collapsed';
                $showClass = !empty($item['active']) ? 'show' : '';

                $html .= '<li class="nav-item">';
                $html .= '<a class="nav-link ' . $activeClass . '" data-bs-toggle="collapse" href="#menu-' . $key . '" role="button" aria-expanded="' . (!empty($item['active']) ? 'true' : 'false') . '" aria-controls="menu-' . $key . '">';
                $html .= '<i class="icon" style="color:#000000;">' . $item['icon'] . '</i>';
                $html .= '<span class="item-name">' . CHtml::encode($item['label']) . '</span>';
                $html .= '<i class="right-icon" style="color:#000000;"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-18"><path d="M8.5 5L15.5 12L8.5 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg></i>';
                $html .= '</a>';

                $html .= '<ul class="sub-nav collapse ' . $showClass . '" id="menu-' . $key . '" data-bs-parent="#sidebar-menu">';
                foreach ($item['children'] as $childKey => $child) {
                    $html .= self::renderMenuItem($child);
                }
                $html .= '</ul>';
                $html .= '</li>';
            } else {
                // No children - render as single item
                $html .= self::renderMenuItem($item);
            }
        }

        return $html;
    }

    /**
     * Render single menu item
     */
    private static function renderMenuItem($item)
    {
        $activeClass = !empty($item['active']) ? 'active' : '';
        $url = isset($item['url']) ? $item['url'] : '#';

        $html = '<li class="nav-item">';
        $html .= '<a class="nav-link ' . $activeClass . '" href="' . $url . '">';
        if (isset($item['icon'])) {
            $html .= '<i class="icon" style="color:#000000;"><svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                <g>
                                    <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                </g>
                            </svg></i>';
        }
        $html .= '<span class="item-name">' . CHtml::encode($item['label']) . '</span>';
        $html .= '</a>';
        $html .= '</li>';

        return $html;
    }
}
