export const ETDLineStatus = Object.freeze({
    Approved: 'APPROVED',
    Rejected: 'REJECTED',
    Initial: 'INITIAL',
    WaitingForApproval: 'WAITING_FOR_APPROVAL'
});
export const UserType = Object.freeze({Vendor: 'VENDOR', Purchaser: 'PURCHASER'});
export const ETDStatus = Object.freeze({
    WaitingPurchaser: 'WAITING_PURCHASER',
    WaitingVendor: 'WAITING_VENDOR',
    Closed: 'CLOSED'
});

export const BadgeStatusType = Object.freeze({
    ETDStatus: 'ETDStatus',
    ETDLineStatus: 'ETDLineStatus',
    ETDLineTag: 'ETDLineTag'
});

export const ETDLineTag = Object.freeze({
    Closed: 'Closed',
    Completed: 'Completed',
    ETDChanged: 'ETDChanged',
    ShipByChanged: 'ShipByChanged',
    Partial: 'Partial',
    QtyChanged: 'QtyChanged',
});

export const ETDScreenType = Object.freeze({
    Active: 'ACTIVE',
    Open: 'OPEN',
    Archive: 'ARCHIVE',
});